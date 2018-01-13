<?php

namespace PHPAnnotations\Reflection;

use PHPAnnotations\Utils\Utils;
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
        $tmp = Utils::StringsBetween($tmp, '[', ']');

        foreach ($tmp as $annotation)
        {
            if (Utils::StringContains($annotation, '('))
            {
                $args = Utils::StringBetween($annotation, '(', ')');

                if (Utils::StringContainsExcludingBetween($args, ',', "\"", "\""))
                {
                    $args = Utils::ReplaceTokens($args, [', '=>',']);
                    $args = Utils::Split($args, ',');

                    $namedArgs = [];

                    foreach ($args as $arg)
                    {
                        if (!Utils::StringContains($arg, '=')) continue;

                        $tokens = Utils::ReplaceTokens($arg, [' = ' => '=', ' =' => '=', '= ' => '=']);
                        $tokens = Utils::Split($tokens, '=');

                        $namedArgs[$tokens[0]] = $tokens[1];
                    }

                    $args = $namedArgs;
                }
                else
                {
                    if (Utils::StringContains($args, '='))
                    {
                        $args = Utils::ReplaceTokens($args, [' =' => '=', '= ', '=', ' = ', '=']);
                        $args = Utils::Split($args, '=');

                        $args = [$args[0] => $args[1]];
                    }
                    elseif ($args !== "") $args = [$args];
                }

                foreach ($args as $k => $v)
                {
                    $v = trim($v);

                    if (Utils::StringStartsWith($v, "'") && Utils::StringEndsWith($v, "'"))
                    {
                        $args[$k] = Utils::StringBetween($v, "'", "'");
                    }
                    elseif (Utils::StringStartsWith($v, '"') && Utils::StringEndsWith($v, '"'))
                    {
                        $args[$k] = Utils::StringBetween($v, '"', '"');
                    }
                    elseif (Utils::StringEquals(strtolower($v), 'true'))
                    {
                        $args[$k] = true;
                    }
                    elseif (Utils::StringEquals(strtolower($v), 'false'))
                    {
                        $args[$k] = false;
                    }
                    else
                    {
                        if (Utils::StringContains($v, '.')) $args[$k] = floatval($v);
                        else $args[$k] = intval($v);
                    }
                }

                $aClass = Utils::StringBefore($annotation, '(');

                if (!Utils::StringContains($aClass, 'Annotation')) $aClass .= 'Annotation';

                if (!is_subclass_of($aClass, 'PHPAnnotations\Annotations\Annotation')) continue;

                $instance = null;

                if (method_exists($aClass, '__construct'))
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
                    $instance = $refClass->newInstanceArgs((array)$reArgs);

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
            else
            {
                $aClass = $annotation;

                if (!Utils::StringContains($aClass, 'Annotation'))
                {
                    $aClass .= 'Annotation';
                }

                if (!is_subclass_of($aClass, 'PHPAnnotations\Annotations\Annotation')) continue;

                $instance = $this->FillInstance(new $aClass());
                $obj->annotations[$aClass] = $instance;
            }
        }
    }

    private function FillInstance($instance)
    {
        $instance->obj = $this->object;
        echo is_array($this->annotations) ? "Si" : "No";
        $instance->reflections = $this->annotations;

        return $instance;
    }
}