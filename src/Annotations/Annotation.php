<?php

namespace PHPAnnotations\Annotations;

class Annotation
{
    protected $obj;

    public function __set($key, $value)
    {
        if (!property_exists($this, $key))
            throw new \Exception("Param $key does not exist in " . get_class($this) . " annotation!");

        $trace = debug_backtrace();
        $class = $trace[1]['class'];

        if ('\PHPAnnotations\Reflection\Reflector' === $class ||
            'PHPAnnotations\Reflection\Reflector' === $class ||
            is_a($class, '\PHPAnnotations\Reflection\ReflectionBase', true) ||
            is_a($class, 'PHPAnnotations\Reflection\ReflectionBase', true))
        {
            $this->$key = $value;
        }
    }

    /**
     * __get magic method used to retrieve the name.
     * @param string $param
     * @return null
     */
    public function __get($param)
    {
        $result = null;

        if (property_exists($this, $param)) $result = $this->$param;

        return $result;
    }
}