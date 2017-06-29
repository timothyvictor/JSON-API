<?php

namespace TimothyVictor\JsonAPI\Test;

class ClientErrorTest extends TestCase
{
    public function test_a_request_with_incorrect_content_type_header_returns_an_error_response()
    {
        // $this->disableExceptionHandling();
        // no headers set
        $response = $this->json('GET', '/categories');
        $content = json_decode($response->getContent());
        // exit(dump($content));
        $this->assertValidJsonApiStructure($content);

        $response
            ->assertStatus(415)
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonStructure([
                'errors' => [
                    [
                        'status',
                        'title',
                        'detail',
                    ],
                ],
                'jsonapi',
            ]);
    }

    public function test_server_responds_with_406_if_accept_header_json_api_is_present_without_params()
    {
        $this->disableExceptionHandling();
        $accept_headers = [
            'Accept' => [
                'application/vnd.api+json;parameters=naughty',
                'application/vnd.api+json;parameters=double_naughty',
            ],
        ];
        $headers = array_merge($accept_headers, $this->getContentTypeHeader());
        $response = $this->json('GET', '/categories/1', [], $headers);
        $content = json_decode($response->getContent());
        $this->assertValidJsonApiStructure($content);

        $response
            ->assertStatus(406)
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonStructure([
                'errors' => [
                    [
                        'status',
                        'title',
                        'detail',
                    ],
                ],
                'jsonapi',
            ]);
    }

    public function test_the_server_responds_400_with_errors_for_malfored_json_request_body()
    {
        $this->disableExceptionHandling();
        $resource_object = [
            'data' => [
                'type'       => 'categories',
                'attributes' => [
                    'title'       => 'Music',
                    'description' => 'White lines, blow away',
                ],
                'title'       => 'Music',
                'description' => 'White lines, blow away',
            ],
        ];
        $response = $this->postJson('/categories', $resource_object, $this->getHeaders());
        $response
            ->assertStatus(400)
            ->assertHeader('Content-Type', 'application/vnd.api+json');
        $content = json_decode($response->getContent());
        $this->assertValidJsonApiStructure($content);
    }
}
