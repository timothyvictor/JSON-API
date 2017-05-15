<?php

namespace TimothyVictor\JsonAPI\Test;

use TimothyVictor\JsonAPI\Helper;

class HelperTest extends TestCase
{
    public function test_comma_to_array_converts_a_comma_seperated_string_to_an_array()
    {
        $string = "one,two,three,four,five";
        $array = Helper::comma_to_array($string);
        $this->assertTrue(is_array($array));
        $this->assertEquals(["one","two","three","four","five"], $array);
    }

    public function test_comma_to_array_returns_an_array_contiaining_the_original_string_if_no_commas_are_present()
    {
        $string = "one two three four five";
        $array = Helper::comma_to_array($string);
        $emptyArray = Helper::comma_to_array('');

        $this->assertTrue(is_array($array));
        $this->assertEquals(["one two three four five"], $array);
        $this->assertTrue(is_array($emptyArray));
        $this->assertEquals([""], $emptyArray);
    }

    public function test_dot_to_array_returns_a_flat_array_from_dot_string()
    {
        $string = "one.two.three.four";
        $actual = Helper::dot_to_array($string);
        $expected = ['one', 'two', 'three', 'four'];
        $this->assertTrue(is_array($actual));
        $this->assertEquals($expected, $actual);
    }

}