<?php

namespace Dropbox\Controller;

use Dropbox\Service\Dropbox;
use Dropbox\Form\AboutForm;
use Dropbox\Form\FileForm;
use Laminas\Form\Form;
use Laminas\Form\Element;
use Laminas\Form\http_build_query;
use Laminas\View\Model\ViewModel;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Form\Annotation\AttributeBuilder;
use Laminas\Form\Element\Submit;
use Laminas\Http\Client;

class IndexController extends AbstractActionController
{

    /**
    * @param Dropbox $dropbox
	* @var FileForm 
    */
    public function __construct(public Dropbox $dropbox)
    {
        $this->dropbox = $dropbox;
        
    }

    public function aboutAction()
    {
        
        $aboutForm = new AboutForm();

        $this->fileForm = $aboutForm;
        //-------------------------------------------------------
        $this->fileForm->get('plik')->setValue('Wprowadź nazwe dla pliku tekstowego bez rozszerzenia');
        $this->fileForm->get('plik')->setAttribute('readonly', 'readonly');
        $this->fileForm->get('plik')->setAttribute('size', '50');

        $ViewModel = new ViewModel(['form' => $aboutForm]);


        return $ViewModel;
    }

    public function indexAction()
    {
        if (!$this->dropbox->isAuthorized()) {
            return $this->redirect()->toRoute('dropbox/default', ['action' => 'authorize']);
        }
        $path = $this->params()->fromQuery('path', '');
        $files = $this->dropbox->getFileList($path);

        return ['files' => $files];
    }

    public function fileAction()
    {
        if (!$this->dropbox->isAuthorized()) {
            return $this->redirect()->toRoute('dropbox/default', ['action' => 'authorize']);
        }
        $path = $this->params()->fromQuery('path', '');

        // tworzy obiekt formularza
        $fileForm = new FileForm(); 
        $this->fileForm = $fileForm;
        $this->fileForm->get('zapisz')->setValue(value:'Dodaj');

        // Metoda getRequest pobiera wszystko z formularza
        $request = $this->getRequest();

        // Sprawdza czy zapytanie wysłane metodą POST/czyli wciśnięcie button wyślij
        if ($request->isPost()) 
        {
            //$fileName = $this->getRequest()->getPost()->nazwaPliku;

            // Dane które mamy w tablicy POST przekazujemy do formularza
            $this->fileForm->setData($request->getPost(), $path);

            // Sprawdza czy wypełnione są pola jak TRUE to wykonuje jak FALSE wyswietla błedy i zwraca formularz
            if ($this->fileForm->isValid()) 
            {
                $parameters = $this->dropbox->addFileDropbox($request->getPost(), $path);
                return $this->redirect()->toRoute('dropbox');
                // To samo co -> return $this->redirect()->toRoute('dropbox/default',['action' => 'index']);
            }   
        }

        $viewModel = new ViewModel(['tytul' => 'Dodawanie nowego pliku do dropbox',
                                    'form' =>$this->fileForm]);
        return $viewModel;
    }

    public function editAction()
    {
        if (!$this->dropbox->isAuthorized()) {
            return $this->redirect()->toRoute('dropbox/default', ['action' => 'authorize']);
        }
                
        $path = $this->params()->fromQuery('path', '');
        $name = $this->params()->fromQuery('name', '');

        //--------------------------------------------------------
        $fileForm = new FileForm(); // tworzy obiekt formularza
        $this->fileForm = $fileForm;
        $this->fileForm->get('zapisz')->setValue(value:'Aktualizuj');
        // Wypełnia i pole formularza 'nazwaPliku' nazwa pliku i ustawia tryb readonly
        $this->fileForm->get('nazwaPliku')->setValue($name);
        $this->fileForm->get('nazwaPliku')->setAttribute('readonly', 'readonly');
        // Pobierz zawartość pliku
        $fileContent = $this->dropbox->getFileContentDropbox($path);
        // Ustawia pole formularza 'trescPliku' zawartością
        $this->fileForm->get('trescPliku')->setValue($fileContent);

        $request = $this->getRequest();

        if ($request->isPost()) 
        {
            $this->fileForm->setData($request->getPost(), $path);

            if ($this->fileForm->isValid()) 
            {
                $parameters = $this->dropbox->updateFileDropbox($request->getPost(), $path);
                return $this->redirect()->toRoute('dropbox');
            }   
        }

        $viewModel = new ViewModel(['tytul' => 'Edycja pliku w dropbox',
                                    'form' =>$this->fileForm]);
        return $viewModel;  
    }

    public function authorizeAction()
    {
        return ['authorize_url' => $this->dropbox->generateAuthorizationUrl()];
    }

    public function finishAction()
    {
        $code = $this->params()->fromQuery('code');

        $msg = '';

        try {
            $result = $this->dropbox->getAccessToken($code);

            if ($result === true) {
                return $this->redirect()->toRoute('dropbox');
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
        }

        return ['msg' => $msg];
    }

    public function deleteAction()
    {
        if (!$this->dropbox->isAuthorized()) {
            return $this->redirect()->toRoute('dropbox/default', ['action' => 'authorize']);
        }
        $path = $this->params()->fromQuery('path', '');

        $this->dropbox->fileToDelete($path);
        $this->redirect()->toRoute('dropbox');

    }

    public function downloadAction()
    {
        if (!$this->dropbox->isAuthorized()) {
            return $this->redirect()->toRoute('dropbox/default', ['action' => 'authorize']);
        }
        $request = $this->getRequest();

        if ($request->isGet()) {
            $file = $this->params()->fromQuery('path');
            $content=$this->dropbox->downloadFileDropbox($file);
            $path = tempnam(sys_get_temp_dir(), 'prefix');
            $handle = fopen($path, "w");
            fwrite($handle , $content);
            fclose($handle);
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' .str_replace('/','',$file) . '"');
            header('Content-Length: ' . filesize($path));
            header('Content-Description: File Transfer');
            header('Pragma: public');

            readfile($path);
            return $this->response;
        } 
        return $this->redirect()->toRoute('dropbox');
    }

}

