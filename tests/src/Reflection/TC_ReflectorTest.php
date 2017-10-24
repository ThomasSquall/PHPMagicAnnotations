<?php

include_once dirname(__FILE__) . '/../../MyTestAnnotation.php';
include_once dirname(__FILE__) . '/../../MyTestClass.php';

use \PHPAnnotations\Reflection\TC_Reflector;

class TC_ReflectorTest extends PHPUnit_Framework_TestCase
{
    public function testHasAnnotationTrue()
    {
        $myClass = new MyTestClass();
        $reflector = new TC_Reflector($myClass);

        $this->assertTrue($reflector->getClass()->hasAnnotation("MyTest"));
    }

    public function testHasAnnotationFalse()
    {
        $myClass = new MyTestClass();
        $reflector = new TC_Reflector($myClass);

        $this->assertFalse($reflector->getClass()->hasAnnotation("NotMyTest"));
    }

    public function testGetAnnotation()
    {
        $expected = "Thomas Cocchiara";
        $myClass = new MyTestClass();
        $reflector = new TC_Reflector($myClass);

        $this->assertEquals($expected,
            $reflector->getClass()->getAnnotation("MyTest")->GetFullName());
    }

    public function testGetAnnotationNull()
    {
        $myClass = new MyTestClass();
        $reflector = new TC_Reflector($myClass);

        $this->assertNull($reflector->getClass()->getAnnotation("NotMyTest"));
    }
}