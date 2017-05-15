<?php

namespace TimothyVictor\JsonAPI\Test;

use TimothyVictor\JsonAPI\Test\Resources\Models\Category;
use TimothyVictor\JsonAPI\Responder;

class ResponderTest extends TestCase
{
    protected $topLevelMandatoryMembers = ['data', 'errors', 'meta'];

    protected function containsMustHaveMembers($json)
    {
        return !empty(array_intersect($this->topLevelMandatoryMembers, array_keys($json)));
    }

    public function test_a_valid_collection_generates_a_valid_repsonse()
    {
        $collection = factory(Category::class, 5)->create();
        $responder = $this->app->make(Responder::class);
        $response = $responder->respondWithCollection($collection);
        $headers = $response->headers->all();
        $content = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->status());
        $this->assertTrue(array_key_exists('content-type', $headers));
        $this->assertTrue(in_array('application/vnd.api+json', $headers['content-type']));
        $this->assertTrue(array_key_exists('data', $content));
        $this->assertTrue($this->containsMustHaveMembers($content));
    }

    public function test_a_valid_item_generates_a_valid_repsonse()
    {
        $item = factory(Category::class)->create();
        $responder = $this->app->make(Responder::class);
        $response = $responder->respondWithResource($item);
        $headers = $response->headers->all();
        $content = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->status());
        $this->assertTrue(array_key_exists('content-type', $headers));
        $this->assertTrue(in_array('application/vnd.api+json', $headers['content-type']));
        $this->assertTrue(array_key_exists('data', $content));
        $this->assertTrue($this->containsMustHaveMembers($content));
    }

    public function test_include_to_array_generates_correct_array_shape()
    {
        $string = "one,two.three,four.five.six";
        $responder = $this->app->make(Responder::class);
        $array = $responder->include_to_array($string);
        $expected = ['one','two.three','four.five.six'];
        $this->assertEquals($expected, $array);
    }

}