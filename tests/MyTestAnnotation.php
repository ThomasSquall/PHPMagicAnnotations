<?php

namespace tests;

use PHPAnnotations\Annotations\Annotation;

class MyTestAnnotation extends Annotation
{
    protected $name;
    protected $surname;

    public function __construct($name, $surname)
    {
        $this->name = $name;
        $this->surname = $surname;
    }

    public function GetFullName()
    {
        return "$this->name $this->surname";
    }

    public function GetObject()
    {
        return $this->obj;
    }
}