<?php

namespace PHPAnnotations\Annotations;

use PHPAnnotations\Utils\Utils;

class Annotation
{
    protected $obj;

    public function __set($key, $value)
    {
        $trace = debug_backtrace();
        $class = $trace[1]['class'];

        if (Utils::StringEquals('\PHPAnnotations\Reflection\Reflector', $class)
         || Utils::StringEquals('PHPAnnotations\Reflection\Reflector', $class))
        {
            $this->$key = $value;
        }
    }
}