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

    public function test_respond_unsupported_media_type()
    {
        $responseClass = $this->app->make(Response::class);
        $response = $responseClass->respondUnsupportedMediaType();
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

}