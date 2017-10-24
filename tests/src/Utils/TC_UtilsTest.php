<?php

use \PHPAnnotations\Utils\TC_Utils;

class TC_UtilsTest extends PHPUnit_Framework_TestCase
{
    public function testStringStartsWithTrue()
    {
        $this->assertTrue(TC_Utils::StringStartsWith('ThisIsASample', 'This'));
    }

    public function testStringStartsWithFalse()
    {
        $this->assertFalse(TC_Utils::StringStartsWith('ThisIsASample', 'this'));
    }

    public function testStringEqualsTrue()
    {
        $this->assertTrue(TC_Utils::StringEquals('Equal', 'Equal'));
    }

    public function testStringEqualsFalse()
    {
        $this->assertFalse(TC_Utils::StringEquals('Equal', 'notEqual'));
    }

    public function testStringEndsWithTrue()
    {
        $this->assertTrue(TC_Utils::StringEndsWith('ThisIsASample', 'Sample'));
    }

    public function testStringEndsWithFalse()
    {
        $this->assertFalse(TC_Utils::StringEndsWith('ThisIsASample', 'sample'));
    }

    public function testStringBefore()
    {
        $expected = "test";

        $this->assertEquals($expected,
            TC_Utils::StringBefore('testThisPlease', 'T'));
    }

    public function testStringBeforeFalse()
    {
        $this->assertFalse(TC_Utils::StringBefore('testThisPlease', 'Leviathan'));
    }

    public function testStringContainsTrue()
    {
        $this->assertTrue(TC_Utils::StringContains('testThisPlease', 'This'));
    }

    public function testStringContainsFalse()
    {
        $this->assertFalse(TC_Utils::StringContains('testThisPlease', 'Leviathan'));
    }

    public function testReplaceTokens()
    {
        $expected = "Hello World!";

        $this->assertEquals($expected,
            TC_Utils::ReplaceTokens('testThisPlease',
                [
                    "test" => "Hello",
                    "This" => " ",
                    "Please" => "World!"
                ]
        ));
    }

    public function testSplit()
    {
        $expected = [
            'Hello',
            'World!'
        ];

        $this->assertEquals($expected,
            TC_Utils::Split("Hello World!", " "));
    }

    public function testStringContainsExcludingBetweenTrue()
    {
        $this->assertTrue(TC_Utils::StringContainsExcludingBetween("Test1Test2Test", "Test", "1", "2"));
    }

    public function testStringContainsExcludingBetweenFalse()
    {
        $this->assertFalse(TC_Utils::StringContainsExcludingBetween("No1Test2No", "Test", "1", "2"));
    }
}