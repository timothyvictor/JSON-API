<?php

namespace TimothyVictor\JsonAPI\Test;

use TimothyVictor\JsonAPI\Response;

class ResponseTest extends TestCase
{
    public function test_ok_responds_ok()
    {
        $responseClass = $this->app->make(Response::class);
        $response = $responseClass->ok();
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
            'jsonapi' => [
                'version' => '1.0',
            ],
            'errors' => [
                [
                    'title'  => 'Unsupported Media Type',
                    'detail' => 'Clients MUST send all JSON API data in request documents with the header "Content-Type: application/vnd.api+json" without any media type parameters.',
                    'status' => '415',
                ],
            ],
        ];
        $content = $response->getOriginalContent();
        $this->assertEquals($expectedContent, $content);
    }

    public function test_resource_created()
    {
        $responseClass = $this->app->make(Response::class);
        $response = $responseClass->resourceCreated(['data' => []], 'https://www.the-link-to-the-resource.com/resource/1');
        $this->assertEquals(201, $response->status(), 'response code is 201');

        $headers = $response->headers->all();
        $this->assertTrue(array_key_exists('content-type', $headers), 'content-type header is present');
        $this->assertTrue(array_key_exists('location', $headers), 'location header is present');
        $this->assertTrue(in_array('application/vnd.api+json', $headers['content-type']), 'content-type header has correct valye');
        $this->assertTrue(in_array('https://www.the-link-to-the-resource.com/resource/1', $headers['location']), 'location header has correct value');
    }

    public function test_bad_request_handles_multiple_errors()
    {
        $errors = [
            [
                'description' => 'error one',
                'pointer'     => 'in the bag',
                'status'      => '400',
            ],
            [
                'description' => 'error two',
                'pointer'     => 'in the pocket',
                'status'      => '400',
            ],
            [
                'description' => 'error three',
                'pointer'     => 'in the road',
                'status'      => '400',
            ],
        ];
        $responseClass = $this->app->make(Response::class);
        $response = $responseClass->badRequest($errors);
        $this->assertEquals(400, $response->status(), 'response code is 400');
        $expectedContent = [
            'jsonapi' => [
                'version' => '1.0',
            ],
            'errors' => [
                [
                    'description' => 'error one',
                    'pointer'     => 'in the bag',
                    'status'      => '400',
                ],
                [
                    'description' => 'error two',
                    'pointer'     => 'in the pocket',
                    'status'      => '400',
                ],
                [
                    'description' => 'error three',
                    'pointer'     => 'in the road',
                    'status'      => '400',
                ],

            ],
        ];
        $content = $response->getOriginalContent();
        $this->assertEquals($expectedContent, $content);
    }

    public function test_resource_accepted()
    {
        $responseClass = $this->app->make(Response::class);

        $response1 = $responseClass->accepted();
        $response2 = $responseClass->accepted('Your wonderful request is baking in the oven, but not quite cooked');
        $content1 = $response1->getOriginalContent();
        $content2 = $response2->getOriginalContent();
        $this->assertEquals(202, $response1->status());
        $this->assertEquals(202, $response2->status());

        $this->assertEquals('Your request has been accepted, but is still being processed', $content1['meta']['message'], 'default meta message is correct');
        $this->assertEquals('Your wonderful request is baking in the oven, but not quite cooked', $content2['meta']['message'], 'custom meta message is correct');
    }

    public function test_no_content()
    {
        $responseClass = $this->app->make(Response::class);
        $response = $responseClass->noContent();
        $this->assertEquals(204, $response->status());
        $content = $response->getContent();
        $this->assertEquals($content, '');
    }

    public function test_not_found()
    {
        $responseClass = $this->app->make(Response::class);
        $message = 'The requested resource could not be found';
        $response = $responseClass->notFound($message);
        $this->assertEquals(404, $response->status());
        $content = $response->getOriginalContent();
        $this->assertEquals($message, $content['errors'][0]['detail']);
        // dump($content);
    }
}
