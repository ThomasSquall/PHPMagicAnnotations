<?php

use PHPAnnotations\Annotations\Annotation;

class OneArgAnnotation extends Annotation
{
    protected $myArg;

    public function __construct($arg)
    {
        $this->myArg = $arg;
    }
}