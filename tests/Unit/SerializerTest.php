<?php

namespace TimothyVictor\JsonAPI\Test;

use TimothyVictor\JsonAPI\Serializer;
use TimothyVictor\JsonAPI\Test\Resources\Models\Category;
use TimothyVictor\JsonAPI\Test\Resources\Models\Article;
use TimothyVictor\JsonAPI\Test\Resources\Models\Author;
use TimothyVictor\JsonAPI\Test\Resources\Models\Comment;

class SerializerTest extends TestCase
{
    
    public function test_serialize_relationships_returns_a_relationship_object()
    {
        $category = factory(Category::class)->create();
        $articles = $category->articles()->saveMany(factory(Article::class, 3)->make());
        // dd($category->articles);

        $serializer = $this->app->make(Serializer::class);

        $relationships = $serializer->serializeRelationships($category);

        // dd($relationships);

        $this->assertTrue(is_array($relationships));
        // test that id and type are not key in the array
        $this->assertTrue(array_key_exists('relationships', $relationships));
        // dump($relationships);
        $this->assertTrue(array_key_exists('articles', $relationships['relationships']));
    }

    public function test_serialize_relationship_returns_an_empty_array_for_an_empty_has_many_relation()
    {
        $category = factory(Category::class)->create();
        $serializer = $this->app->make(Serializer::class);
        $relationships = $serializer->serializeRelationships($category);
        $this->assertTrue(is_array($relationships));
        $this->assertTrue(array_key_exists('relationships', $relationships));
        $this->assertTrue(array_key_exists('articles', $relationships['relationships']));
        $this->assertTrue(is_array($relationships['relationships']));
        $this->assertTrue(empty($relationships['relationships']['articles']['data']));
        // dump($relationships['relationships']['articles']);
    }

    public function test_serialize_relationship_returns_a_resource_identifier_object_for_a_present_to_one_relation()
    {
        $article = factory(Article::class)->create();
        $category = $article->category()->associate(factory(Category::class)->create());
        $serializer = $this->app->make(Serializer::class);
        $relationships = $serializer->serializeRelationships($article);
        $this->assertTrue(is_array($relationships));
        $this->assertTrue(array_key_exists('relationships', $relationships));
        $this->assertTrue(is_array($relationships['relationships']));
        $this->assertTrue(array_key_exists('category', $relationships['relationships']));
        $authorResourceIdentifier = $relationships['relationships']['category']['data'];
        $this->assertTrue(is_array($authorResourceIdentifier));
        $this->assertTrue(array_key_exists('type', $authorResourceIdentifier));
        $this->assertTrue(array_key_exists('id', $authorResourceIdentifier));
    }
    
    public function test_serialize_relationship_returns_null_for_an_empty_to_one_relation()
    {
        $article = factory(Article::class)->create();
        $serializer = $this->app->make(Serializer::class);
        $relationships = $serializer->serializeRelationships($article);
        $this->assertTrue(is_array($relationships));
        $this->assertTrue(array_key_exists('relationships', $relationships));
        $this->assertTrue(array_key_exists('category', $relationships['relationships']));
        $this->assertTrue(is_array($relationships['relationships']));
        // dump($relationships);
        $this->assertTrue(gettype($relationships['relationships']['category']['data']) === "NULL");
        
    }

    public function test_serialize_relationship_includes_a_links_object_for_each_relationship()
    {
        $article = factory(Article::class)->create();
        $category = $article->category()->associate(factory(Category::class)->create());
        // $category = factory(Category::class)->create();
        $comments = $article->comments()->saveMany(factory(Comment::class, 3)->create());
        // $author = $category->author()->associate(factory(Author::class)->create());
        // $author->save();
        $serializer = $this->app->make(Serializer::class);
        $relationships = $serializer->serializeRelationships($article);
        $categoryObject = $relationships['relationships']['category'];
        $commentsObject = $relationships['relationships']['comments'];
        // dump($relationships);
        $this->assertTrue(array_key_exists('links', $categoryObject));
        $this->assertTrue(array_key_exists('self', $categoryObject['links']));
        $this->assertTrue(array_key_exists('links', $commentsObject));
        $this->assertTrue(array_key_exists('self', $commentsObject['links']));


    }

    public function test_top_level_links_object_returns_paginator_members_for_a_paginated_collection(){
        factory(Category::class, 10)->create();
        $paginator = Category::paginate(2);
        $parameters = [];
        $parameters['pagination'] = [
            'first' => $paginator->url(1),
            'last' => $paginator->url($paginator->lastPage()),
            'prev' => $paginator->previousPageUrl(),
            'next' => $paginator->nextPageUrl(),
        ];
        $serialize = $this->app->make(Serializer::class);
        $linksObject = $serialize->topLevelLinksObject($paginator->getCollection(), $parameters);
        $this->assertTrue(array_key_exists('links', $linksObject));
        $this->assertTrue(array_key_exists('self', $linksObject['links']));
        $this->assertTrue(array_key_exists('pagination', $linksObject['links']));
        $expectedKeys = ['first', 'last', 'prev', 'next'];
        $this->assertTrue(empty(array_diff($expectedKeys, array_keys($linksObject['links']['pagination']))));
    }

    public function test_top_level_links_object_generates_correct_links()
    {
        // need to do this for collection too
        // also maybe sort out transformSelfLink - need method for collection and resource
        $category = factory(Category::class)->create();
        $serialize = $this->app->make(Serializer::class);
        $linksObject = $serialize->topLevelLinksObject($category, []);
        $this->assertEquals($linksObject['links']['self'], $category->transformSelfLink() . "/{$category->id}");
    }
}