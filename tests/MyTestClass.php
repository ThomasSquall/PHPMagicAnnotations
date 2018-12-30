<?php

/**
 * @MyTest(name = "Thomas", surname = "{$surname}")
 */
class MyTestClass
{
    const TEST_CONSTANT = "TEST";

    /**
     * @var int $age
     */
    var $age = 27;
    var $surname = "Cocchiara";

    public function callTest() {}

    /**
     * @OneArg(arg = ["ciao", "due"])
     * @NoConstructor
     */
    public function callTest2() {}
}