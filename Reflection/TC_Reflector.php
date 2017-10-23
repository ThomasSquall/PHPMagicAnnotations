<?php

/**
 * Extended class for reflection.
 * Class TC_Reflector.
 */
class TC_Reflector
{
    /**
     * Used to get all the properties.
     */
    const ALL_PROPERTIES = ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE;

    /**
     * Used to get all the methods.
     */
    const ALL_METHODS = ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED | ReflectionMethod::IS_PRIVATE;

    /**
     * The object to reflect.
     * @var StdClass $object
     */
    private $object;

    /**
     * The reflected object.
     * @var ReflectionClass $reflected
     */
    private $reflected;

    /**
     * Array of object annotations.
     * @var TC_ReflectionBase[] $annotations
     */
    private $annotations;

    /**
     * TC_Reflector constructor.
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

        $this->reflected = new ReflectionClass($object);
        $this->prepareAnnotations();
    }

    /**
     * Returns the reflected base object
     * @return ReflectionClass
     */
    public function getReflectedObject()
    {
        return $this->reflected;
    }

    /**
     * Returns the reflected class.
     * @return TC_ReflectionClass
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
     * @return array[TC_ReflectionProperty[]]
     */
    public function getProperties()
    {
        return $this->annotations['properties'];
    }

    /**
     * Returns the reflected parameter.
     * @param string $name
     * @return TC_ReflectionProperty
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
     * @return array[TC_ReflectionMethod[]]
     */
    public function getMethods()
    {
        return $this->annotations['methods'];
    }

    /**
     * Returns the reflected method.
     * @param string $name
     * @return TC_ReflectionMethod
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

        $class = new TC_ReflectionClass($this->reflected->name);
        $this->calculateAnnotations($class, $this->reflected->getDocComment());
        $docs['class'] = $class;

        $properties = $this->reflected->getProperties(self::ALL_PROPERTIES);

        foreach ($properties as $p)
        {
            $property = new TC_ReflectionProperty();
            $this->calculateAnnotations($property, $p->getDocComment());
            $docs['properties'][$p->getName()] = $property;
        }

        $methods = $this->reflected->getMethods(self::ALL_PROPERTIES);

        foreach ($methods as $m)
        {
            $method = new TC_ReflectionMethod();
            $this->calculateAnnotations($method, $m->getDocComment());
            $docs['methods'][$m->getName()] = $method;
        }

        $this->annotations = $docs;
    }

    private function calculateAnnotations(TC_ReflectionBase &$obj, $docs)
    {
        $tmp = trim(preg_replace('/\s\s+/', ' ', $docs));
        $tmp = TC_Utils::StringsBetween($tmp, '[', ']');

        foreach ($tmp as $annotation)
        {
            if (TC_Utils::StringContains($annotation, '('))
            {
                $args = TC_Utils::StringBetween($annotation, '(', ')');

                if (TC_Utils::StringContainsExcludingBetween($args, ',', "\"", "\""))
                {
                    $args = TC_Utils::ReplaceTokens($args, [', '=>',']);
                    $args = TC_Utils::Split($args, ',');

                    $namedArgs = [];
                    foreach ($args as $arg)
                    {
                        if (!TC_Utils::StringContains($arg, '=')) continue;

                        $tokens = TC_Utils::ReplaceTokens($arg, [' = ' => '=', ' =' => '=', '= ' => '=']);
                        $tokens = TC_Utils::Split($tokens, '=');

                        $namedArgs[$tokens[0]] = $tokens[1];
                    }

                    $args = $namedArgs;
                }
                else
                {
                    if (TC_Utils::StringContains($args, '='))
                    {
                        $args = TC_Utils::ReplaceTokens($args, [' =' => '=', '= ', '=', ' = ', '=']);
                        $args = TC_Utils::Split($args, '=');

                        $args = [$args[0] => $args[1]];
                    }
                    elseif ($args !== "") $args = [$args];
                }

                foreach ($args as $k => $v)
                {
                    $v = trim($v);

                    if (TC_Utils::StringStartsWith($v, "'") && TC_Utils::StringEndsWith($v, "'"))
                    {
                        $args[$k] = TC_Utils::StringBetween($v, "'", "'");
                    }
                    elseif (TC_Utils::StringStartsWith($v, '"') && TC_Utils::StringEndsWith($v, '"'))
                    {
                        $args[$k] = TC_Utils::StringBetween($v, '"', '"');
                    }
                    elseif (TC_Utils::StringEquals(strtolower($v), 'true'))
                    {
                        $args[$k] = true;
                    }
                    elseif (TC_Utils::StringEquals(strtolower($v), 'false'))
                    {
                        $args[$k] = false;
                    }
                    else
                    {
                        if (TC_Utils::StringContains($v, '.')) $args[$k] = floatval($v);
                        else $args[$k] = intval($v);
                    }
                }

                $aClass = TC_Utils::StringBefore($annotation, '(');

                if (!TC_Utils::StringContains($aClass, 'Annotation')) $aClass .= 'Annotation';

                if (!is_subclass_of($aClass, 'TC_Annotation')) continue;

                $instance = null;

                if (method_exists($aClass, '__construct'))
                {
                    $refMethod = new ReflectionMethod($aClass,  '__construct');
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

                    $refClass = new ReflectionClass($aClass);
                    $instance = $refClass->newInstanceArgs((array) $reArgs);
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

                    $obj->annotations[$aClass] = $instance;
                }
            }
            else
            {
                $aClass = $annotation;

                if (!TC_Utils::StringContains($aClass, 'Annotation'))
                {
                    $aClass .= 'Annotation';
                }

                if (!is_subclass_of($aClass, 'TC_Annotation')) continue;

                $obj->annotations[$aClass] = new $aClass();
            }
        }
    }
}