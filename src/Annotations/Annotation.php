<?php

namespace PHPAnnotations\Annotations;

class Annotation
{
    protected $obj;

    public function __set($key, $value)
    {
        $trace = debug_backtrace();
        $class = $trace[1]['class'];

        if ('\PHPAnnotations\Reflection\Reflector' === $class
            || 'PHPAnnotations\Reflection\Reflector' === $class)
        {
            $this->$key = $value;
        }
    }
}