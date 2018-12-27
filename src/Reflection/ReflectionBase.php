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
        $result = false;

        if (!string_contains($name, 'Annotation')) $name .= 'Annotation';

        foreach ($this->annotations as $annotation)
        {
            if (is_a($annotation, $name))
            {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * Returns the requested annotation.
     * @param string $name
     * @return Annotation|null
     */
    public function getAnnotation($name)
    {
        $result = null;

        if ($this->hasAnnotation($name))
        {
            if (!string_contains($name, 'Annotation')) $name .= 'Annotation';

            $result = $this->annotations[$name];
            $result = $this->evaluateAnnotation($result);
        }

        return $result;
    }

    private function evaluateAnnotation(Annotation $annotation)
    {
        $reflected = new \ReflectionClass($annotation);
        $properties = $reflected->getProperties(
            \ReflectionProperty::IS_PUBLIC |
            \ReflectionProperty::IS_PROTECTED
        );

        foreach ($properties as $p)
        {
            $key = $p->name;
            $value = $annotation->$key;

            if (is_string($value) && string_contains($value, '{$'))
            {
                $fields = strings_between($value, '{$', '}');

                $tokens = [];

                foreach ($fields as $field)
                {
                    $v = property_exists($annotation->obj, $field) ? $annotation->obj->$field : "";
                    $tokens['{$' . $field . '}'] = $v;
                }

                $value = $this->replaceTokens($value, $tokens);

                $annotation->$key = $value;
            }
        }

        return $annotation;
    }

    private function replaceTokens($text, array $replace)
    {
        foreach ($replace as $token => $value)
        {
            $text = str_replace($token, $value, $text);
        }

        return $text;
    }
}