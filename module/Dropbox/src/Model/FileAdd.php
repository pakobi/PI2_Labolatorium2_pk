<?php

namespace Dropbox\Model;

use Laminas\Form\Annotation;
use Laminas\Hydrator\ObjectPropertyHydrator;

#[Annotation\Name('file')]
#[Annotation\Hydrator(ObjectPropertyHydrator::class)]
class FileAdd
{
    #[Annotation\Exclude]
    public int $id;

    #[Annotation\Options(["label" => "Plik"])]
    #[Annotation\Required]
    public string $FileName;

    #[Annotation\Options(["label" => "Zawartosc"])]
    #[Annotation\Required]
    public string $FileContent;
}