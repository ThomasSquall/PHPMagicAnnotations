<?php

use PHPAnnotations\Annotations\Annotation;

class MyTestAnnotation extends Annotation
{
    private $name;
    private $surname;

    public function __construct($name, $surname)
    {
        $this->name = $name;
        $this->surname = $surname;
    }

    public function GetFullName()
    {
        return "$this->name $this->surname";
    }
}