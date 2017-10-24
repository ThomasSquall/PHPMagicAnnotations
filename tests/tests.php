<?php

include_once __DIR__ . "/../vendor/autoload.php";

include_once __DIR__ . "/../src/autoload.php";

include_once "MyTestAnnotation.php";
include_once "MyTestClass.php";

$object = new MyTestClass();
$reflector = new \PHPAnnotations\Reflection\TC_Reflector($object);

echo $reflector->getClass()->getAnnotation("MyTest")->GetFullName() . PHP_EOL;