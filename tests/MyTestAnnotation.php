<?php

use PHPAnnotations\Annotations\TC_Annotation;

class MyTestAnnotation extends TC_Annotation
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