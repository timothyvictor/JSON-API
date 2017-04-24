<?php

namespace TimothyVictor\JsonAPI\Test;

use TimothyVictor\JsonAPI\Test\Resources\Models\Category;
use TimothyVictor\JsonAPI\Test\Resources\Models\Article;
use TimothyVictor\JsonAPI\Test\Resources\Models\Author;

class JsonApiControllerTest extends TestCase
{

    public function test_a_get_request_to_a_resource_returns_a_collection_of_the_resource()
    {

        // \Artisan::call('route:list', [
            // 'command_parameter_1' => 'value1',
            // 'command_parameter_2' => 'value2',
        // ]);
        // $routes = \Artisan::output();
        // dd($routes);

        $this->disableExceptionHandling();

        $categories = factory(Category::class, 5)->create();
        
        $response = $this->json('GET', '/categories');

        $this->assertValidJsonApiStructure(json_decode($response->getContent()));

        $response
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'type',
                    ]
                ],
                'links',
                'jsonapi'
            ])
            ->assertJson([
                'jsonapi' => [ 
                    'version' =>  '1.0' 
                ],
            ])
            ->assertJson([
                'data' => [ 
                    [
                        'type' => 'categories'
                    ]
                ],
            ]);
    }

    public function test_a_get_request_to_a_resource_with_an_id_returns_the_resource()
    {
        $this->disableExceptionHandling();

        $category = factory(Category::class)->create();
        $id = $category->id;
        
        $response = $this->json('GET', "/categories/{$id}");
        // dd(json_decode($response->getContent()));
        $this->assertValidJsonApiStructure(json_decode($response->getContent()));

        $response
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJson([
                'jsonapi' => [ 
                    'version' =>  '1.0' 
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $id,
                    'type' => 'categories'
                ],
            ]);
    }

    public function test_a_get_request_to_resource_with_a_relationship_contains_the_relationships_member()
    {
        // $this->disableExceptionHandling();
        // factory(Author::class)->create();
        $categories = factory(Category::class, 5)->create();
        $categories->each(function($category){
            $author = factory(Author::class)->create();
            $category->articles()->saveMany(factory(Article::class, rand(1, 5))->make());
            $category->author()->associate($author)->save();

        });
        
        $response = $this->json('GET', '/categories');
        
        $this->assertValidJsonApiStructure(json_decode($response->getContent()));

        $response
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'type',
                        'relationships' => [
                            'articles' => [
                                'data' => [
                                    ['type', 'id']
                                ],
                                'links'
                            ],
                            'author' => [
                                'data' => [
                                    'type',
                                    'id'
                                ],
                                'links'
                            ]
                        ]
                    ]
                ],
                'links',
                'jsonapi'
            ])
            ->assertJson([
                'jsonapi' => [ 
                    'version' =>  '1.0' 
                ],
            ]);
    }


    // public function test_response_from_json_endpoint_return_an_error_if_request_headers_do_have_correct_media_type()
    // {
        
    // }
}