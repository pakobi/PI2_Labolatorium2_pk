<?php

namespace SoapClient\Model;

use Laminas\Form\Annotation;
use Laminas\Hydrator\ObjectPropertyHydrator;

#[Annotation\Name('movie')]
#[Annotation\Hydrator(ObjectPropertyHydrator::class)]
class Movie
{
    #[Annotation\Exclude]
    public int $id;

    #[Annotation\Options(["label" => "Tytuł"])]
    #[Annotation\Required]
    public string $title;

    #[Annotation\Options(["label" => "Rok"])]
    #[Annotation\Required]
    public int $year;

    #[Annotation\Options(["label" => "Ocena"])]
    #[Annotation\Required]
    public float $rating;

    #[Annotation\Options(["label" => "Link"])]
    #[Annotation\Required]
    public string $link;

    #[Annotation\Options(["label" => "ID gatunku"])]
    #[Annotation\Required]
    public int $genreId;
}