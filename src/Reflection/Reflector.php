<?php

namespace PHPAnnotations\Reflection;

use \ReflectionProperty as RP;
use \ReflectionMethod as RM;
use \ReflectionClass as RC;
use \stdClass as stdClass;
use \Exception as Exception;

/**
 * Extended class for reflection.
 * Class Reflector.
 */
class Reflector
{
    /**
     * Used to get all the properties.
     */
    const ALL_PROPERTIES = RP::IS_PUBLIC | RP::IS_PROTECTED | RP::IS_PRIVATE;

    /**
     * Used to get all the methods.
     */
    const ALL_METHODS = RM::IS_PUBLIC | RM::IS_PROTECTED | RM::IS_PRIVATE;

    /**
     * The object to reflect.
     * @var stdClass $object
     */
    private $object = null;

    /**
     * The reflected object.
     * @var RC $reflected
     */
    private $reflected;

    /**
     * Array of object annotations.
     * @var ReflectionBase[] $annotations
     */
    private $annotations;

    /**
     * Reflector constructor.
     * @param mixed $object
     * @throws Exception
     */
    public function __construct($object)
    {
        if (is_null($object))
            throw new Exception("Cannot evaluate null!");

        if (!is_object($object))
            throw new Exception("Works with objects only!");

        $this->object = $object;

        $this->reflected = new RC($object);
        $this->prepareAnnotations();
    }

    /**
     * Returns the reflected class.
     * @return ReflectionClass
     */
    public function getClass()
    {
        return $this->annotations['class'];
    }

    /**
     * Returns the object constants.
     * @return array
     */
    public function getConstants()
    {
        return $this->reflected->getConstants();
    }

    /**
     * Returns the reflected properties.
     * @return array[ReflectionProperty[]]
     */
    public function getProperties()
    {
        return $this->annotations['properties'];
    }

    /**
     * Returns the reflected parameter.
     * @param string $name
     * @return ReflectionProperty
     */
    public function getProperty($name)
    {
        if ($this->reflected->hasProperty($name))
            return $this->annotations['properties'][$name];

        return null;
    }

    /**
     * Returns the reflected methods.
     * @return array[ReflectionMethod[]]
     */
    public function getMethods()
    {
        return $this->annotations['methods'];
    }

    /**
     * Returns the reflected method.
     * @param string $name
     * @return ReflectionMethod
     */
    public function getMethod($name)
    {
        if ($this->reflected->hasMethod($name))
            return $this->annotations['methods'][$name];

        return null;
    }

    private function prepareAnnotations()
    {
        $docs =
        [
            'class' => [],
            'properties' => [],
            'methods' => []
        ];

        $class = new ReflectionClass($this->reflected->name);
        $this->calculateAnnotations($class, $this->reflected->getDocComment());
        $docs['class'] = $class;

        $properties = $this->reflected->getProperties(self::ALL_PROPERTIES);

        foreach ($properties as $p)
        {
            $property = new ReflectionProperty();
            $this->calculateAnnotations($property, $p->getDocComment());
            $docs['properties'][$p->getName()] = $property;
        }

        $methods = $this->reflected->getMethods(self::ALL_METHODS);

        foreach ($methods as $m)
        {
            $method = new ReflectionMethod();
            $this->calculateAnnotations($method, $m->getDocComment());
            $docs['methods'][$m->getName()] = $method;
        }

        $this->annotations = $docs;
    }

    private function calculateAnnotations(ReflectionBase &$obj, $docs)
    {
        $docs = str_replace("\r", "", $docs);
        $tmp = strings_between($docs, '@', "\n");

        foreach ($tmp as $annotation)
        {
            if (string_contains($annotation, '('))
                $this->calculateAnnotationsWithArgs($obj, $annotation);
            else
                $this->calculateAnnotationsWithoutArgs($obj, $annotation);
        }
    }

    private function calculateAnnotationsWithArgs(&$obj, $annotation)
    {
        $args = string_between($annotation, '(', ')');

        if ($this->stringContainsExcludingBetween($args, ',', "\"", "\"") &&
            $this->stringContainsExcludingBetween($args, ',', "[", "]"))
            $args = $this->calculateMultipleArgs($args);
        else
            $args = $this->calculateSingleArg($args);

        foreach ($args as $k => $v)
            $args[$k] = $this->parseArg($v);

        $aClass = $this->stringBefore($annotation, '(');

        $this->getAnnotationsClass($aClass);

        $instance = null;

        if (is_null($aClass))
            return;

        if (method_exists($aClass, '__construct'))
            $instance = $this->instanceFromConstructor($aClass, $args);
        else
            $instance = new $aClass();

        if ($instance != null)
        {
            if (count($args) > 0)
            {
                foreach ($args as $key => $value)
                {
                    if (!property_exists($instance, $key)) continue;
                    $instance->$key = $value;
                }
            }

            $instance = $this->fillInstance($instance);
            $obj->annotations[$aClass] = $instance;
        }
    }

    private function calculateAnnotationsWithoutArgs(&$obj, $annotation)
    {
        $aClass = $annotation;

        $this->getAnnotationsClass($aClass);

        if (is_null($aClass))
            return;

        $instance = $this->fillInstance(new $aClass());
        $obj->annotations[$aClass] = $instance;
    }

    private function calculateMultipleArgs($args)
    {
        $args = $this->replaceTokens($args, [', ' => ',']);
        $args = explode(',', $args);

        $namedArgs = [];

        foreach ($args as $arg)
        {
            if (!string_contains($arg, '=')) continue;

            $tokens = $this->replaceTokens($arg, [' = ' => '=', ' =' => '=', '= ' => '=']);
            $tokens = explode('=', $tokens);

            $namedArgs[$tokens[0]] = $tokens[1];
        }

        return $namedArgs;
    }

    private function calculateSingleArg($args)
    {
        if (string_contains($args, '='))
        {
            $args = $this->replaceTokens($args, [' =' => '=', '= ', '=', ' = ', '=']);
            $args = explode('=', $args);

            $args = [$args[0] => $args[1]];
        }
        elseif ($args !== "") $args = [$args];

        return $args;
    }

    private function fillInstance($instance)
    {
        $instance->obj = $this->object;

        return $instance;
    }

    private function parseArg($value)
    {
        $v = trim($value);

        if (string_starts_with($v, "'") && string_ends_with($v, "'"))
            $result = string_between($v, "'", "'");
        elseif (string_starts_with($v, '"') && string_ends_with($v, '"'))
            $result = string_between($v, '"', '"');
        elseif (string_starts_with($v, '[') && string_ends_with($v, ']'))
            $result = explode(',', string_between($v, '[', ']'));
        elseif (strtolower($v) === 'true')
            $result= true;
        elseif (strtolower($v) === 'false')
            $result = false;
        else
        {
            if (string_contains($v, '.')) $result = floatval($v);
            else $result = intval($v);
        }

        return $result;
    }

    private function instanceFromConstructor($aClass, &$args)
    {
        $refMethod = new RM($aClass,  '__construct');
        $params = $refMethod->getParameters();

        $reArgs = [];

        foreach ($params as $key => $param)
        {
            $name = $param->getName();

            if (isset($args[$name])) $key = $name;

            if (!isset($args[$key]))
            {
                $reArgs[$key] = null;
                continue;
            }

            if ($param->isPassedByReference()) $reArgs[$key] = &$args[$key];
            else $reArgs[$key] = $args[$key];

            unset($args[$key]);
        }

        $refClass = new RC($aClass);

        return $refClass->newInstanceArgs((array)$reArgs);
    }

    private function stringBefore($string, $before)
    {
        $result = false;

        if (string_contains($string, $before))
        {
            $tmp = explode($before, $string);
            $result = $tmp[0];
        }

        return $result;
    }

    private function stringContainsExcludingBetween($where, $find, $start, $end)
    {
        $between = string_between($where, $start, $end);
        $where = $this->replaceTokens($where, [$start . $between . $end => ""]);

        return string_contains($where, $find);
    }

    private function replaceTokens($text, array $replace)
    {
        foreach ($replace as $token => $value)
        {
            $text = str_replace($token, $value, $text);
        }

        return $text;
    }

    private function getAnnotationsClass(&$aClass)
    {
        if (!string_ends_with($aClass, 'Annotation'))
            $aClass .= 'Annotation';

        $possibleAnnotations = [];

        if (!string_contains($aClass, "\\"))
        {
            foreach (get_declared_classes() as $class)
                if (string_ends_with($class, $aClass))
                    $possibleAnnotations[] = $class;

            $count = count($possibleAnnotations);

            if ($count > 1)
            {
                $message = "";
                $foundSame = false;

                foreach ($possibleAnnotations as $index => $annotation)
                {
                    if ($annotation === $aClass)
                    {
                        $possibleAnnotations[0] = $annotation;
                        $foundSame = true;
                        break;
                    }

                    switch ($index)
                    {
                        case $count - 2;
                            $message .= "$annotation and ";
                            break;
                        case $count - 1;
                            $message .= "$annotation ";
                            break;
                        default:
                            $message .= "$annotation , ";
                            break;
                    }
                }

                if (!$foundSame)
                {
                    $message .= "all satisfy the $aClass. Please use a full namespace instead.";
                    throw new Exception($message);
                }
            }

            if ($count != 0)
                $aClass = $possibleAnnotations[0];
            else
                $aClass = null;
        }

        if (!is_subclass_of($aClass, 'PHPAnnotations\Annotations\Annotation'))
            $aClass = null;
    }
}