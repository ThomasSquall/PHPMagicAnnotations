<?php

namespace PHPAnnotations\Reflection;

class TC_ReflectionClass extends TC_ReflectionBase
{
    public $BaseClass;

    public function __construct($class)
    {
        $this->BaseClass = $class;
    }

    public function getInheritedAnnotation($name)
    {
        $parent = $this->BaseClass;
        $annotation = null;

        do
        {
            if ($parent === false) continue;

            $reflector = new TC_Reflector($parent);
            $annotation = $reflector->getClass()->getAnnotation($name);
            if ($annotation != null) break;
        }
        while ($parent !== false);

        return $annotation;
    }
}