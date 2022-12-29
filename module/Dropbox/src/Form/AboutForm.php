<?php

namespace Dropbox\Form;

use Laminas\Form\Form;
use Laminas\Form\Element;
use Laminas\InputFilter\InputFilterProviderInterface;

class AboutForm extends Form implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('about');
        $this->setAttributes(['method' => 'post', 'class' => 'form']);

        $this->add([
            'name' => 'plik',
            'type' => 'Text',
            'options' => [
                'label' => 'Nazwa pliku',
            ],
            'attributes' => ['class' => 'form-control'],
        ]);
        $this->add([
            'name' => 'zawartoscPliku',
            'type' => 'Text',
            'options' => [
                'label' => 'Zawartość pliku',
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
                'name' => 'plik',
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