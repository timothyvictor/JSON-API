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

    public function test_passing_the_wrong_class_generates_a_type_error()
    {
        $error;
        $responder = $this->app->make(Responder::class);
        try {
            $response = $responder->respondWithCollection(new \stdClass);
        } catch(\TypeError $error) {
            return;
        }
        // dump($error);
        $this->assertTrue($error instanceof \TypeError);

    }

    public function test_respond_created_generates_correct_response()
    {
        $resource = factory(Category::class)->create();
        $responder = $this->app->make(Responder::class);
        $response = $responder->respondResourceCreated($resource);
        $headers = $response->headers->all();
        $content = json_decode($response->getContent(), true);

        $this->assertEquals(201, $response->status(), 'has 201 status');
        $this->assertTrue(array_key_exists('content-type', $headers), 'has content-type headers');
        $this->assertTrue(array_key_exists('location', $headers), 'has location headers');
        $this->assertTrue(in_array('application/vnd.api+json', $headers['content-type']), 'has correct content type headers');
        // exit(dump($headers['location']));
        $this->assertEquals($resource->transformSelfLink() . "/{$resource->id}", $headers['location'][0], 'has correct location header value');
        $this->assertTrue(array_key_exists('data', $content), 'content has data key');
        $this->assertTrue($this->containsMustHaveMembers($content), 'content has all the must have member keys');

    }
}