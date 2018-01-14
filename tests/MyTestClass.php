<?php

/**
 * [MyTest(name = "Thomas", surname = "{$surname}")]
 */
class MyTestClass
{
    const TEST_CONSTANT = "TEST";

    var $age = 27;
    var $surname = "Cocchiara";

    public function callTest() {}

    /**
     * [OneArg("Nice")]
     * [NoConstructor]
     */
    public function callTest2() {}
}