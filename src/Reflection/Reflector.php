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
    private $object;

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
        if (is_object($object))
        {
            $this->object = $object;
        }
        else
        {
            $this->object = null;
        }

        $this->reflected = new RC($object);
        $this->prepareAnnotations();
    }

    /**
     * Returns the reflected base object
     * @return RC
     */
    public function getReflectedObject()
    {
        return $this->reflected;
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
        {
            return $this->annotations['properties'][$name];
        }

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
        {
            return $this->annotations['methods'][$name];
        }

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

        $methods = $this->reflected->getMethods(self::ALL_PROPERTIES);

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
        $tmp = trim(preg_replace('/\s\s+/', ' ', $docs));
        $tmp = $this->stringsBetween($tmp, '[', ']');

        foreach ($tmp as $annotation)
        {
            if ($this->stringContains($annotation, '('))
            {
                $this->calculateAnnotationsWithArgs($obj, $annotation);
            }
            else
            {
                $this->calculateAnnotationsWithoutArgs($obj, $annotation);
            }
        }
    }

    private function calculateAnnotationsWithArgs(&$obj, $annotation)
    {
        $args = $this->stringBetween($annotation, '(', ')');

        if ($this->stringContainsExcludingBetween($args, ',', "\"", "\""))
        {
            $args = $this->calculateMultipleArgs($args);
        }
        else $args = $this->calculateSingleArg($args);

        foreach ($args as $k => $v) $args[$k] = $this->parseArg($v);

        $aClass = $this->stringBefore($annotation, '(');

        if (!$this->stringContains($aClass, 'Annotation')) $aClass .= 'Annotation';

        if (!is_subclass_of($aClass, 'PHPAnnotations\Annotations\Annotation')) return;

        $instance = null;

        if (method_exists($aClass, '__construct'))
        {
            $instance = $this->instanceFromConstructor($aClass, $args);
        }
        else $instance = new $aClass();

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

            $instance = $this->FillInstance($instance);
            $obj->annotations[$aClass] = $instance;
        }
    }

    private function calculateAnnotationsWithoutArgs(&$obj, $annotation)
    {
        $aClass = $annotation;

        if (!$this->stringContains($aClass, 'Annotation'))
        {
            $aClass .= 'Annotation';
        }

        if (!is_subclass_of($aClass, 'PHPAnnotations\Annotations\Annotation')) return;

        $instance = $this->FillInstance(new $aClass());
        $obj->annotations[$aClass] = $instance;
    }

    private function calculateMultipleArgs($args)
    {
        $args = $this->replaceTokens($args, [', ' => ',']);
        $args = explode(',', $args);

        $namedArgs = [];

        foreach ($args as $arg)
        {
            if (!$this->stringContains($arg, '=')) continue;

            $tokens = $this->replaceTokens($arg, [' = ' => '=', ' =' => '=', '= ' => '=']);
            $tokens = explode('=', $tokens);

            $namedArgs[$tokens[0]] = $tokens[1];
        }

        return $namedArgs;
    }

    private function calculateSingleArg($args)
    {
        if ($this->stringContains($args, '='))
        {
            $args = $this->replaceTokens($args, [' =' => '=', '= ', '=', ' = ', '=']);
            $args = explode('=', $args);

            $args = [$args[0] => $args[1]];
        }
        elseif ($args !== "") $args = [$args];

        return $args;
    }

    private function FillInstance($instance)
    {
        $instance->obj = $this->object;

        return $instance;
    }

    private function parseArg($value)
    {
        $v = trim($value);

        if ($this->stringStartsWith($v, "'") && $this->stringEndsWith($v, "'"))
        {
            $result = $this->stringBetween($v, "'", "'");
        }
        elseif ($this->stringStartsWith($v, '"') && $this->stringEndsWith($v, '"'))
        {
            $result = $this->stringBetween($v, '"', '"');
        }
        elseif (strtolower($v) === 'true')
        {
            $result= true;
        }
        elseif (strtolower($v) === 'false')
        {
            $result = false;
        }
        else
        {
            if ($this->stringContains($v, '.')) $result = floatval($v);
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

    private function stringStartsWith($string, $start)
    {
        return substr($string, 0, strlen($start)) === $start;
    }

    private function stringEndsWith($string, $end)
    {
        $length = strlen($end);

        if ($length == 0) return true;

        return substr($string, -$length) === $end;
    }

    private function stringBefore($string, $before)
    {
        if ($this->stringContains($string, $before))
        {
            $tmp = explode($before, $string);
            return $tmp[0];
        }

        return false;
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

    private function stringContains($where, $find)
    {
        return strpos($where, $find) !== false;
    }

    private function stringContainsExcludingBetween($where, $find, $start, $end)
    {
        $between = $this->stringBetween($where, $start, $end);
        $where = $this->replaceTokens($where, [$start . $between . $end => ""]);

        return $this->stringContains($where, $find);
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