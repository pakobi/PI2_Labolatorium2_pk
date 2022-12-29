<?php

namespace Dropbox\Form;

use Laminas\Form\Form;
use Laminas\Form\Element;
use Laminas\InputFilter\InputFilterProviderInterface;

class FileForm extends Form implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('dropbox');
        $this->setAttributes(['method' => 'post', 'class' => 'form']);

        $this->add([
            'name' => 'nazwaPliku',
            'type' => Element\Text::class,
            'options' => [
                'label' => 'Nazwa pliku',
                'placeholder' => 'Wprowadź nazwę pliku bez rozszerzenia'
            ],
            'attributes' => [
                'required' => true,
                'class' => 'form-control',
                'pattern' => '^[a-zA-Z0-9.]+$'
            ],
        ]);
        $this->add([
            'name' => 'trescPliku',
            'type' => Element\Textarea::class,
            'options' => [
                'label' => 'Treść pliku',
            ],
            'attributes' => ['class' => 'form-control'],
        ]);
        $this->add([
            'name' => 'zapisz',
            'type' => 'Submit',
            'attributes' => [
                'value' => 'Dodaj plik',
                'class' => 'btn btn-primary',
            ],
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            [
                'name' => 'nazwaPliku',
                'required' => true,
                'filters' => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
                'validators' => [],
            ],
            [
                'name' => 'trescPliku',
                'required' => true,
                'filters' => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
                'validators' => [],
            ],
        ];
    }
}