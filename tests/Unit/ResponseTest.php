<?php

namespace TimothyVictor\JsonAPI\Test;

use TimothyVictor\JsonAPI\Response;

class ResponseTest extends TestCase
{
    

    public function test_respond_ok_responds_ok()
    {
        $responseClass = $this->app->make(Response::class);
        $response = $responseClass->respondOk([]);
        $this->assertEquals(200, $response->status());

        $headers = $response->headers->all();
        $this->assertTrue(array_key_exists('content-type', $headers));
        $this->assertTrue(in_array('application/vnd.api+json', $headers['content-type']));
    }

}