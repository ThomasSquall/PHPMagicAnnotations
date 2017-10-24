<?php

namespace PHPAnnotations\Reflection;

use PHPAnnotations\Annotations\TC_Annotation;
use PHPAnnotations\Utils\TC_Utils;

abstract class TC_ReflectionBase
{
    /**
     * The array containing our annotations.
     * @var TC_Annotation[] $annotations
     */
    public $annotations = [];

    /**
     * Tells if the reflection object has the given annotation or not.
     * @param string $name
     * @return bool
     */
    public function hasAnnotation($name)
    {
        if (!TC_Utils::StringContains($name, 'Annotation')) $name .= 'Annotation';

        foreach ($this->annotations as $annotation)
        {
            if (is_a($annotation, $name)) return true;
        }

        return false;
    }

    /**
     * Returns the requested annotation.
     * @param string $name
     * @return TC_Annotation|null
     */
    public function getAnnotation($name)
    {
        if ($this->hasAnnotation($name))
        {
            if (!TC_Utils::StringContains($name, 'Annotation')) $name .= 'Annotation';

            return $this->annotations[$name];
        }

        return null;
    }
}