<?php

namespace TimothyVictor\JsonAPI\Test;

use TimothyVictor\JsonAPI\Response;

class ResponseTest extends TestCase
{
    

    public function test_ok_responds_ok()
    {
        $responseClass = $this->app->make(Response::class);
        $response = $responseClass->ok([]);
        $this->assertEquals(200, $response->status());

        $headers = $response->headers->all();
        $this->assertTrue(array_key_exists('content-type', $headers));
        $this->assertTrue(in_array('application/vnd.api+json', $headers['content-type']));
    }

    public function test_unsupported_media_type()
    {
        $responseClass = $this->app->make(Response::class);
        $response = $responseClass->unsupportedMediaType();
        $this->assertEquals(415, $response->status());
        $expectedContent = [
            "jsonapi" => [
                "version" => "1.0"
            ],
            'errors' => [
                [
                    "title" => "Unsupported Media Type",
                    'detail' => 'Clients MUST send all JSON API data in request documents with the header "Content-Type: application/vnd.api+json" without any media type parameters.',
                    'status' => "415"
                ]
            ]
        ];
        $content = $response->getOriginalContent();
        $this->assertEquals($expectedContent, $content);



        // exit(dump($content));
    }

    public function test_resource_created(){
        $responseClass = $this->app->make(Response::class);
        $response = $responseClass->resourceCreated(['data' => []], 'https://www.the-link-to-the-resource.com/resource/1');
        $this->assertEquals(201, $response->status(), 'response code is 201');

        $headers = $response->headers->all();
        $this->assertTrue(array_key_exists('content-type', $headers), 'content-type header is present');
        $this->assertTrue(array_key_exists('location', $headers), 'location header is present');
        $this->assertTrue(in_array('application/vnd.api+json', $headers['content-type']), 'content-type header has correct valye');
        $this->assertTrue(in_array('https://www.the-link-to-the-resource.com/resource/1', $headers['location']), 'location header has correct value');
        // exit(dump($response->getContent()));

    }

}