<?php

use \PHPAnnotations\Utils\Utils;

class UtilsTest extends PHPUnit_Framework_TestCase
{
    public function testStringStartsWithTrue()
    {
        $this->assertTrue(Utils::StringStartsWith('ThisIsASample', 'This'));
    }

    public function testStringStartsWithFalse()
    {
        $this->assertFalse(Utils::StringStartsWith('ThisIsASample', 'this'));
    }

    public function testStringEqualsTrue()
    {
        $this->assertTrue(Utils::StringEquals('Equal', 'Equal'));
    }

    public function testStringEqualsFalse()
    {
        $this->assertFalse(Utils::StringEquals('Equal', 'notEqual'));
    }

    public function testStringEndsWithTrue()
    {
        $this->assertTrue(Utils::StringEndsWith('ThisIsASample', 'Sample'));
    }

    public function testStringEndsWithFalse()
    {
        $this->assertFalse(Utils::StringEndsWith('ThisIsASample', 'sample'));
    }

    public function testStringBefore()
    {
        $expected = "test";

        $this->assertEquals($expected,
            Utils::StringBefore('testThisPlease', 'T'));
    }

    public function testStringBeforeFalse()
    {
        $this->assertFalse(Utils::StringBefore('testThisPlease', 'Leviathan'));
    }

    public function testStringContainsTrue()
    {
        $this->assertTrue(Utils::StringContains('testThisPlease', 'This'));
    }

    public function testStringContainsFalse()
    {
        $this->assertFalse(Utils::StringContains('testThisPlease', 'Leviathan'));
    }

    public function testReplaceTokens()
    {
        $expected = "Hello World!";

        $this->assertEquals($expected,
            Utils::ReplaceTokens('testThisPlease',
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
            Utils::Split("Hello World!", " "));
    }

    public function testStringContainsExcludingBetweenTrue()
    {
        $this->assertTrue(Utils::StringContainsExcludingBetween("Test1Test2Test", "Test", "1", "2"));
    }

    public function testStringContainsExcludingBetweenFalse()
    {
        $this->assertFalse(Utils::StringContainsExcludingBetween("No1Test2No", "Test", "1", "2"));
    }
}