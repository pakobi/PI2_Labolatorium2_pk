<?php

namespace SoapClient\Controller;

use Laminas\Form\Annotation\AttributeBuilder;
use Laminas\Form\Element\Submit;
use Laminas\Mvc\Controller\AbstractActionController;
use SoapClient\Model\Movie;
use SoapClient\Soap\Client;

class IndexController extends AbstractActionController
{
    /**
     * @param Client $client
     */
    public function __construct(public Client $client)
    {
    }

    public function indexAction()
    {
        return ['movies' => $this->client->fetchMovies()];
    }

    public function addAction()
    {
        $builder = new AttributeBuilder();
        /** TWORZY FORMULARZ NA PODSTAWIE ADNOTACJI */
        $form = $builder->createForm(Movie::class);
        /** DODAJEMY KLAWISZ SAVE */
        $form->add(new Submit('save', ['label' => 'Zapisz']));

        //dd($this->getRequest()->getPost);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                try {
                    $this->client->add($form->getData());
                    $this->flashMessenger()->addSuccessMessage('Rekord został dodany');
                } catch (\SoapFault $e) {
                    $this->flashMessenger()->addErrorMessage($e->getMessage());
                }
            }

            return $this->redirect()->toRoute('soap-client', ['action' => 'add']);
        }

        return ['form' => $form];
    }

    public function editAction()
    {
        $builder = new AttributeBuilder();
        /** TWORZY FORMULARZ NA PODSTAWIE ADNOTACJI */
        $form = $builder->createForm(Movie::class);
        /** DODAJEMY KLAWISZ SAVE */
        $form->add(new Submit('save', ['label' => 'Zapisz']));
        
        $id = (int)$this->params()->fromRoute('id');
        if (empty($id)) {
            $this->redirect()->toRoute('soap-client', ['action' => 'edit']);
        }

        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                try {
                    $this->client->aktualizuj($id, $form->getData()); // czy nie tak $form->getData()
                    $this->flashMessenger()->addSuccessMessage('Rekord został zaktualizowany');
                } catch (\SoapFault $e){
                    $this->flashMessenger()->addErrorMessage($e->getMessage());

                }
            }
            return $this->redirect()->toRoute('soap-client', ['action' => 'edit']);

        } else {
            $daneMovies = $this->client->pobierz($id);
            //dd($daneMovies);
            $form->setData($daneMovies);
        }
        
    return ['form' => $form];
    }


    public function usunAction()
    {
        $id = (int)$this->params()->fromRoute('id');
        if (empty($id)) {
            $this->redirect()->toRoute('soap-client', ['action' => 'usun']);
        }

        $this->client->usun($id);
        $this->flashMessenger()->addSuccessMessage('Rekord został usunięty');
        return $this->redirect()->toRoute('soap-client', ['action' => 'usun']);
    }
}