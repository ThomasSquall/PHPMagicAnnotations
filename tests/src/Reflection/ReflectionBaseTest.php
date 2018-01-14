<?php

include_once dirname(__FILE__) . '/../../MyTestAnnotation.php';
include_once dirname(__FILE__) . '/../../MyTestClass.php';

use \PHPAnnotations\Reflection\Reflector;

class ReflectionBaseTest extends PHPUnit_Framework_TestCase
{
    public function testHasAnnotationTrue()
    {
        $myClass = new MyTestClass();
        $reflector = new Reflector($myClass);

        $this->assertTrue($reflector->getClass()->hasAnnotation("MyTest"));
    }

    public function testHasAnnotationFalse()
    {
        $myClass = new MyTestClass();
        $reflector = new Reflector($myClass);

        $this->assertFalse($reflector->getClass()->hasAnnotation("NotMyTest"));
    }

    public function testGetAnnotation()
    {
        $expected = "Thomas Cocchiara";
        $myClass = new MyTestClass();
        $reflector = new Reflector($myClass);

        $this->assertEquals($expected,
            $reflector->getClass()->getAnnotation("MyTest")->GetFullName());

        $this->assertInstanceOf('MyTestClass',
            $reflector->getClass()->getAnnotation("MyTest")->GetObject());
    }

    public function testGetAnnotationNull()
    {
        $myClass = new MyTestClass();
        $reflector = new Reflector($myClass);

        $this->assertNull($reflector->getClass()->getAnnotation("NotMyTest"));
    }
}