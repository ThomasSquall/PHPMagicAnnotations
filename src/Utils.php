<?php

function class_has_annotation($object, $annotation)
{
    $class = get_reflected_class($object);

    if (!is_string($annotation))
        throw new Exception("Second argument of class_has_annotation should be a string, " .
            gettype($object) . " given instead.");

    return $class->getClass()->hasAnnotation($annotation);
}

function method_has_annotation($object, $method, $annotation)
{
    $class = get_reflected_class($object);

    if (!is_string($method))
        throw new Exception("Second argument of method_has_annotation should be a string, " .
            gettype($method) . " given instead.");

    if (!is_string($annotation))
        throw new Exception("Third argument of method_has_annotation should be a string, " .
            gettype($annotation) . " given instead.");


    $reflectedMethod = $class->getMethod($method);

    if (is_null($reflectedMethod))
        throw new Exception("Method $method does not exist in " . gettype($class) . " class.");

    /** @var \PHPAnnotations\Reflection\ReflectionMethod $reflectedMethod */
    return $reflectedMethod->hasAnnotation($annotation);
}

function property_has_annotation($object, $property, $annotation)
{
    $class = get_reflected_class($object);

    if (!is_string($property))
        throw new Exception("Second argument of property_has_annotation should be a string, " .
            gettype($property) . " given instead.");

    if (!is_string($annotation))
        throw new Exception("Third argument of property_has_annotation should be a string, " .
            gettype($annotation) . " given instead.");

    $reflectedProperty = $class->getProperty($property);

    if (is_null($reflectedProperty))
        throw new Exception("Property $property does not exist in " . gettype($class) . " class.");

    /** @var \PHPAnnotations\Reflection\ReflectionMethod $reflectedProperty */
    return $reflectedProperty->hasAnnotation($annotation);
}

function get_reflected_class($object)
{
    /** @var [\PHPAnnotations\Reflection\Reflector] $reflectors */
    static $reflectors = [];

    if (is_object($object))
        $class = get_class($object);
    else
        throw new Exception("First argument should be an object, " .
            gettype($object) . " given instead.");

    if (!array_key_exists($class, $reflectors))
        $reflectors[$class] = new \PHPAnnotations\Reflection\Reflector($object);

    return $reflectors[$class];
}