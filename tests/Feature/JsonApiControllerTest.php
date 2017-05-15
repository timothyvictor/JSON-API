<?php

namespace TimothyVictor\JsonAPI\Test;

use TimothyVictor\JsonAPI\Test\Resources\Models\Category;
use TimothyVictor\JsonAPI\Test\Resources\Models\Article;
use TimothyVictor\JsonAPI\Test\Resources\Models\Author;
use TimothyVictor\JsonAPI\Test\Resources\Models\Comment;

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
        $this->disableExceptionHandling();
        $article = factory(Article::class)->create();
        $author = factory(Author::class)->create();
        $article->author()->associate($author)->save();
        $category = factory(Category::class)->create();
        $categories = $article->category()->associate($category)->save();
        
        $response = $this->json('GET', "/articles/{$article->id}");
        // dump($response->getContent());
        $this->assertValidJsonApiStructure(json_decode($response->getContent()));

        $response
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'type',
                    'attributes',
                    'relationships' => [
                        'author' => [
                            'data' => ['type', 'id'],
                            'links'
                        ],
                        'category' => [
                            'data' => ['type', 'id'],
                            'links'
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

    public function test_a_get_request_to_resource_with_a_relationship_and_the_include_parameter_inludes_the_relationship()
    {
        $this->disableExceptionHandling();

        $article = factory(Article::class)->create();
        $comments = $article->comments()->saveMany(factory(Comment::class, 3)->create());
        $authors = factory(Author::class, 2)->create();
        $comments->each(function($comment, $key) use ($authors){
            $comment->author()->associate($authors->random());
            $comment->save();
        });
        $categories = $article->category()->associate(factory(Category::class)->create())->save();
        
        $response = $this->json('GET', "articles/{$article->id}?include=category,comments.author");
        // dump(json_decode($response->getContent()));
        
        $this->assertValidJsonApiStructure(json_decode($response->getContent()));

        $response
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonStructure([
                'data',
                'links',
                'jsonapi',
                'included'
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