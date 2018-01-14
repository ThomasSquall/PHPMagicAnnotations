<?php

namespace PHPAnnotations\Reflection;

use PHPAnnotations\Annotations\Annotation;

abstract class ReflectionBase
{
    /**
     * The array containing our annotations.
     * @var Annotation[] $annotations
     */
    public $annotations = [];

    /**
     * Tells if the reflection object has the given annotation or not.
     * @param string $name
     * @return bool
     */
    public function hasAnnotation($name)
    {
        if (!$this->StringContains($name, 'Annotation')) $name .= 'Annotation';

        foreach ($this->annotations as $annotation)
        {
            if (is_a($annotation, $name)) return true;
        }

        return false;
    }

    /**
     * Returns the requested annotation.
     * @param string $name
     * @return Annotation|null
     */
    public function getAnnotation($name)
    {
        if ($this->hasAnnotation($name))
        {
            if (!$this->StringContains($name, 'Annotation')) $name .= 'Annotation';

            return $this->annotations[$name];
        }

        return null;
    }

    private function StringContains($where, $find)
    {
        return strpos($where, $find) !== false;
    }
}