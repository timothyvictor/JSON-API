<?php

namespace TimothyVictor\JsonAPI\Test;

use TimothyVictor\JsonAPI\Test\Resources\Models\Article;
use TimothyVictor\JsonAPI\Test\Resources\Models\Author;
use TimothyVictor\JsonAPI\Test\Resources\Models\Category;
use TimothyVictor\JsonAPI\Test\Resources\Models\Comment;

class JsonApiControllerTest extends TestCase
{
    public function test_a_get_request_to_a_resource_returns_a_collection_of_the_resource()
    {

        // 		\Artisan::call('route:list', [
                    // 		'command_parameter_1' => 'value1',
                    // 		'command_parameter_2' => 'value2',
                // 		]);
        // 		$routes = \Artisan::output();
        // 		dd($routes);

        $this->disableExceptionHandling();

        $categories = factory(Category::class, 5)->create();

        $response = $this->json('GET', '/categories', [], $this->getHeaders());

        // 		exit(dump(json_decode($response->getContent())));

        $this->assertValidJsonApiStructure(json_decode($response->getContent()));

        $response
                    ->assertStatus(200)
                    ->assertHeader('Content-Type', 'application/vnd.api+json')
                    ->assertJsonStructure([
                        'data' => [
                            [
                                'id',
                                'type',
                            ],
                        ],
                        'links',
                        'jsonapi',
                    ])
                    ->assertJson([
                        'jsonapi' => [
                            'version' => '1.0',
                        ],
                    ])
                    ->assertJson([
                        'data' => [
                            [
                                'type' => 'categories',
                            ],
                        ],
                    ]);
    }

    public function test_a_get_request_to_a_resource_with_an_id_returns_the_resource()
    {
        $this->disableExceptionHandling();

        $category = factory(Category::class)->create();
        $id = $category->id;

        $response = $this->json('GET', "/categories/{$id}", [], $this->getHeaders());
        // 		dd(json_decode($response->getContent()));
        $this->assertValidJsonApiStructure(json_decode($response->getContent()));

        $response
                    ->assertStatus(200)
                    ->assertHeader('Content-Type', 'application/vnd.api+json')
                    ->assertJson([
                        'jsonapi' => [
                            'version' => '1.0',
                        ],
                    ])
                    ->assertJson([
                        'data' => [
                            'id'   => $id,
                            'type' => 'categories',
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

        $response = $this->json('GET', "/articles/{$article->id}", [], $this->getHeaders());
        // 		dump($response->getContent());
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
                                    'links',
                                ],
                                'category' => [
                                    'data' => ['type', 'id'],
                                    'links',
                                ],
                            ],
                        ],
                        'links',
                        'jsonapi',
                    ])
                    ->assertJson([
                        'jsonapi' => [
                            'version' => '1.0',
                        ],
                    ]);
    }

    public function test_a_get_request_to_resource_with_a_relationship_and_the_include_parameter_includes_the_relationship()
    {
        $this->disableExceptionHandling();

        $article = factory(Article::class)->create();
        $comments = $article->comments()->saveMany(factory(Comment::class, 3)->create());
        $authors = factory(Author::class, 2)->create();
        $comments->each(function ($comment, $key) use ($authors) {
            $comment->author()->associate($authors->random());
            $comment->save();
        }
        );
        $categories = $article->category()->associate(factory(Category::class)->create())->save();

        $response = $this->json('GET', "articles/{$article->id}?include=category,comments.author", [], $this->getHeaders());
        // 		dump(json_decode($response->getContent()));

        $this->assertValidJsonApiStructure(json_decode($response->getContent()));

        $response
                    ->assertStatus(200)
                    ->assertHeader('Content-Type', 'application/vnd.api+json')
                    ->assertJsonStructure([
                        'data',
                        'links',
                        'jsonapi',
                        'included',
                    ])
                    ->assertJson([
                        'jsonapi' => [
                            'version' => '1.0',
                        ],
                    ]);
    }

    public function test_requesting_fields_in_the_query_string_returns_only_stated_fields()
    {
        // 		$this->disableExceptionHandling();
        $category = factory(Category::class)->create();

        $response = $this->json('GET', "/categories/{$category->id}?fields[categories]=description", [], $this->getHeaders());

        $content = json_decode($response->getContent());
        $this->assertValidJsonApiStructure($content);
        $response
                    ->assertStatus(200)
                    ->assertHeader('Content-Type', 'application/vnd.api+json')
                    ->assertJsonFragment([
                        'attributes' => [
                            'description' => $category->description,
                        ],
                    ]);
    }

    public function test_adding_a_valid_sort_parameter_to_the_query_string_sorts_a_collection_of_primary_data()
    {
        // 		$this->disableExceptionHandling();
        $titles = collect(['toes', 'arm pits', 'noses']);
        $categories = $titles->map(function ($title) {
            return factory(Category::class)->create(['title' => $title]);
        }
        );
        $response = $this->json('GET', '/categories?sort=title', [], $this->getHeaders());
        $response
                    ->assertStatus(200)
                    ->assertHeader('Content-Type', 'application/vnd.api+json');
        $content = json_decode($response->getContent());
        $actualTitles = collect($content->data)->map(function ($category) {
            return $category->attributes->title;
        }
        );
        // 		dump($titles->sort()->flatten()->all(), $actualTitles->flatten()->all());
        $this->assertEquals($titles->sort()->flatten()->all(), $actualTitles->flatten()->all(), 'the returned collection is sorted alphabetically by title');
    }

    public function test_a_response_from_a_paginated_controller_method_includes_the_paginated_links()
    {
        $this->disableExceptionHandling();
        $articles = factory(Article::class, 50)->create();
        $response = $this->json('GET', '/articles', [], $this->getHeaders());
        $content = json_decode($response->getContent());
        $response
                    ->assertStatus(200)
                    ->assertHeader('Content-Type', 'application/vnd.api+json');
        $this->assertValidJsonApiStructure($content);
        $response->assertJsonStructure([
                    'links' => [
                        'self',
                        'pagination' => [
                            'first',
                            'last',
                            'prev',
                            'next',
                        ],
                    ],
                ]);
        $collection_route = route('articles.index');
        $this->assertEquals($collection_route, $content->links->self);
        $this->assertEquals($content->links->pagination->next, $collection_route.'?page=2');
        $this->assertEquals($content->links->pagination->prev, null);

        $response2 = $this->json('GET', $content->links->pagination->next, [], $this->getHeaders());
        $response2
                    ->assertStatus(200)
                    ->assertHeader('Content-Type', 'application/vnd.api+json');
        $this->assertValidJsonApiStructure($content);
        $content2 = json_decode($response2->getContent());
        $this->assertEquals($content2->links->self, $collection_route.'?page=2');
        $this->assertEquals($content2->links->pagination->next, $collection_route.'?page=3');
        $this->assertEquals($content2->links->pagination->prev, $collection_route.'?page=1');
    }

    public function test_posting_a_valid_resource_object_to_a_collection_url_returns_a_201()
    {
        $this->disableExceptionHandling();
        $resource_object = [
                    'data' => [
                        'type'       => 'categories',
                        'attributes' => [
                            'title'       => 'Music',
                            'description' => 'White lines, blow away',
                        ],
                    ],
                ];
        $response = $this->postJson('/categories', $resource_object, $this->getHeaders());
        $response
                    ->assertStatus(201)
                    ->assertHeader('Content-Type', 'application/vnd.api+json')
                    ->assertHeader('Location');
        $this->assertValidJsonApiStructure(json_decode($response->getContent()));
        $locationHeader = $response->headers->get('location');
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($locationHeader, $content['links']['self']);
    }

    // 	this is pretty pointless at the moment. This tests nothing that isn't tested elsewhere. Remove?
    public function test_patching_valid_resource_updates_the_provided_fields()
    {
        $this->disableExceptionHandling();
        $category = factory(Category::class)->create(['title' => 'music']);
        $newTitle = 'painting';
        $resource_object = [
            'data' => [
                'type'       => 'categories',
                'id'         => "{$category->id}",
                'attributes' => [
                    'title' => "{$newTitle}",
                ],
            ],
        ];
        $response = $this->patchJson("/categories/{$category->id}", $resource_object, $this->getHeaders());
        $this->assertValidJsonApiStructure(json_decode($response->getContent()));
        $response
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.api+json');
        $content = json_decode($response->getContent());
        // dump($content['data']['attributes']);
        $this->assertNotEquals($category->title, $content->data->attributes->title);
        $this->assertEquals($newTitle, $content->data->attributes->title);
    }
}
