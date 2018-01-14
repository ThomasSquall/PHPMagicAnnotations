<?php

include_once dirname(__FILE__) . '/../../MyTestAnnotation.php';
include_once dirname(__FILE__) . '/../../OneArgAnnotation.php';
include_once dirname(__FILE__) . '/../../NoConstructorAnnotation.php';
include_once dirname(__FILE__) . '/../../MyTestClass.php';

use \PHPAnnotations\Reflection\Reflector;

class ReflectorTest extends PHPUnit_Framework_TestCase
{
    public function testConstructorWithNullObject()
    {
        $result = false;

        try { new Reflector(null); }
        catch(Exception $ex) { $result = true; }

        $this->assertTrue($result);
    }

    public function testConstructorWithNonObject()
    {
        $result = false;

        try { new Reflector(10); }
        catch(Exception $ex) { $result = true; }

        $this->assertTrue($result);
    }

    public function testConstructorWithObject()
    {
        $result = true;
        $myClass = new MyTestClass();

        try { new Reflector($myClass); }
        catch(Exception $ex) { $result = false; }

        $this->assertTrue($result);
    }

    public function testGetConstants()
    {
        $myClass = new MyTestClass();
        $reflector = new Reflector($myClass);

        $this->assertCount(1, $reflector->getConstants());
    }

    public function testGetProperties()
    {
        $myClass = new MyTestClass();
        $reflector = new Reflector($myClass);

        $this->assertCount(2, $reflector->getProperties());
    }

    public function testGetExistingProperty()
    {
        $myClass = new MyTestClass();
        $reflector = new Reflector($myClass);

        $this->assertNotNull($reflector->getProperty('age'));
    }

    public function testGetNonExistingProperty()
    {
        $myClass = new MyTestClass();
        $reflector = new Reflector($myClass);

        $this->assertNull($reflector->getProperty('fsd'));
    }

    public function testGetMethods()
    {
        $myClass = new MyTestClass();
        $reflector = new Reflector($myClass);

        $this->assertCount(2, $reflector->getMethods());
    }

    public function testGetExistingMethod()
    {
        $myClass = new MyTestClass();
        $reflector = new Reflector($myClass);

        $this->assertNotNull($reflector->getMethod('callTest'));
    }

    public function testGetNonExistingMethod()
    {
        $myClass = new MyTestClass();
        $reflector = new Reflector($myClass);

        $this->assertNull($reflector->getMethod('fwe'));
    }
}