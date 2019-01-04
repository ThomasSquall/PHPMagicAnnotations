<?php

function class_has_annotation($object, $annotation)
{
    $reflector = get_class_reflector($object);

    if (!is_string($annotation))
        throw new Exception("Second argument of class_has_annotation should be a string, " .
            gettype($object) . " given instead.");

    return $reflector->getClass()->hasAnnotation($annotation);
}

function method_has_annotation($object, $method, $annotation)
{
    $reflector = get_class_reflector($object);

    if (!is_string($method))
        throw new Exception("Second argument of method_has_annotation should be a string, " .
            gettype($method) . " given instead.");

    if (!is_string($annotation))
        throw new Exception("Third argument of method_has_annotation should be a string, " .
            gettype($annotation) . " given instead.");

    $reflectedMethod = $reflector->getMethod($method);

    if (is_null($reflectedMethod))
        throw new Exception("Method $method does not exist in " . gettype($object) . " class.");

    /** @var \PHPAnnotations\Reflection\ReflectionMethod $reflectedMethod */
    return $reflectedMethod->hasAnnotation($annotation);
}

function property_has_annotation($object, $property, $annotation)
{
    $reflector = get_class_reflector($object);

    if (!is_string($property))
        throw new Exception("Second argument of property_has_annotation should be a string, " .
            gettype($property) . " given instead.");

    if (!is_string($annotation))
        throw new Exception("Third argument of property_has_annotation should be a string, " .
            gettype($annotation) . " given instead.");

    $reflectedProperty = $reflector->getProperty($property);

    if (is_null($reflectedProperty))
        throw new Exception("Property $property does not exist in " . gettype($object) . " class.");

    /** @var \PHPAnnotations\Reflection\ReflectionMethod $reflectedProperty */
    return $reflectedProperty->hasAnnotation($annotation);
}

function get_class_annotation($object, $annotation)
{
    if (class_has_annotation($object, $annotation))
        return get_class_reflector($object)->getClass()->getAnnotation($annotation);

    return null;
}

function get_method_annotation($object, $method, $annotation)
{
    if (method_has_annotation($object, $method, $annotation))
        return get_class_reflector($object)->getMethod($method)->getAnnotation($annotation);

    return null;
}

function get_property_annotation($object, $property, $annotation)
{
    if (property_has_annotation($object, $property, $annotation))
        return get_class_reflector($object)->getProperty($property)->getAnnotation($annotation);

    return null;
}

function get_class_methods_annotations($object)
{
    return get_class_reflector($object)->getMethods();
}

function get_class_properties_annotations($object)
{
    return get_class_reflector($object)->getProperties();
}

function get_class_reflector($object)
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