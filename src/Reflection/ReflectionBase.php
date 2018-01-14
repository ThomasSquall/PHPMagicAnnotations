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

        if (!$this->stringContains($name, 'Annotation')) $name .= 'Annotation';

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
            if (!$this->stringContains($name, 'Annotation')) $name .= 'Annotation';

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

            if (is_string($value) &&
                $this->stringContains($value, '{$'))
            {
                $fields = $this->stringsBetween($value, '{$', '}');

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

    private function stringContains($where, $find)
    {
        return strpos($where, $find) !== false;
    }

    private function stringBetween($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);

        if ($ini == 0) return false;

        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;

        return substr($string, $ini, $len);
    }

    private function stringsBetween($string, $start, $end)
    {
        $s = $this->stringBetween($string, $start, $end);

        $result = [];

        while (is_string($s))
        {
            $result[] = $s;
            $string = $this->replaceTokens($string, ["$start$s$end" => "----$$$$$$$----"]);
            $s = $this->stringBetween($string, $start, $end);
        }

        return $result;
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