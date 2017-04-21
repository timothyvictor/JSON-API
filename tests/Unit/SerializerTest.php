<?php

namespace TimothyVictor\JsonAPI\Test;

use TimothyVictor\JsonAPI\Serializer;
use TimothyVictor\JsonAPI\Test\Resources\Models\Category;
use TimothyVictor\JsonAPI\Test\Resources\Models\Article;
use TimothyVictor\JsonAPI\Test\Resources\Models\Author;

class SerializerTest extends TestCase
{
    public function test_serialize_collection_returns_a_valid_json_api_strutcure()
    {
        $collection = factory(Category::class, 5)->create();
        $serializer = $this->app->make(Serializer::class);

        $serialized = $serializer->serializeCollection($collection);

        $this->assertTrue($this->arrays_have_same_values($this->topLevelMembers, array_keys($serialized)));
        $this->assertTrue(is_array($serialized['data'][0]));

        $resources = collect($serialized['data']);
        $resources->each(function ($item, $key) {
            $this->assertTrue($this->arrays_have_same_values($this->resourceMembers, array_keys($item)));
        });
    }

    public function test_serialize_resource_returns_a_valid_json_api_strutcure()
    {
        $item = factory(Category::class)->create();
        $serializer = $this->app->make(Serializer::class);

        $serialized = $serializer->serializeResource($item);

        $this->assertTrue($this->arrays_have_same_values($this->topLevelMembers, array_keys($serialized)));

        $this->assertTrue($this->arrays_have_same_values($this->resourceMembers, array_keys($serialized['data'])));
    }

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

    public function test_serialiaze_relationship_returns_an_empty_array_for_an_empty_has_many_relation()
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

    public function test_serialiaze_relationship_returns_a_resource_identifier_object_for_a_present_to_one_relation()
    {
        $category = factory(Category::class)->create();
        $author = $category->author()->associate(factory(Author::class)->create());
        $author->save();
        $serializer = $this->app->make(Serializer::class);
        $relationships = $serializer->serializeRelationships($category);
        $this->assertTrue(is_array($relationships));
        $this->assertTrue(array_key_exists('relationships', $relationships));
        $this->assertTrue(is_array($relationships['relationships']));
        $this->assertTrue(array_key_exists('author', $relationships['relationships']));
        $authorResourceIdentifier = $relationships['relationships']['author']['data'];
        $this->assertTrue(is_array($authorResourceIdentifier));
        $this->assertTrue(array_key_exists('type', $authorResourceIdentifier));
        $this->assertTrue(array_key_exists('id', $authorResourceIdentifier));
    }
    
    public function test_serialiaze_relationship_returns_null_for_an_empty_to_one_relation()
    {
        $category = factory(Category::class)->create();
        $serializer = $this->app->make(Serializer::class);
        $relationships = $serializer->serializeRelationships($category);
        $this->assertTrue(is_array($relationships));
        $this->assertTrue(array_key_exists('relationships', $relationships));
        $this->assertTrue(array_key_exists('articles', $relationships['relationships']));
        $this->assertTrue(is_array($relationships['relationships']));
        $this->assertTrue(gettype($relationships['relationships']['author']['data']) === "NULL");
        
    }
}