<?php

namespace TimothyVictor\JsonAPI\Test;

use TimothyVictor\JsonAPI\Serializer;
use TimothyVictor\JsonAPI\Test\Resources\Models\Category;
use TimothyVictor\JsonAPI\Test\Resources\Models\Article;
use TimothyVictor\JsonAPI\Test\Resources\Models\Author;
use TimothyVictor\JsonAPI\Test\Resources\Models\Comment;

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
    
    public function test_serialiaze_relationship_returns_null_for_an_empty_to_one_relation()
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

    public function test_serialize_relationship_incudes_a_links_object_for_each_relationship()
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

    public function test_get_includes_returns_an_array_of_included_resources(){
        $article = factory(Article::class)->create();
        $comments = $article->comments()->saveMany(factory(Comment::class, 3)->create());
        $authors = factory(Author::class, 2)->create();
        $comments->each(function($comment, $key) use ($authors){
            $comment->author()->associate($authors->random());
            $comment->save();
        });
        $categories = $article->category()->associate(factory(Category::class)->create())->save();

        $includes = ['category','comments.author'];
        // $author = $category->author()->associate(factory(Author::class)->create());
        // $author->save();
        $serializer = $this->app->make(Serializer::class);
        $actual = $serializer->getIncludes($article, $includes);
        // dump($actual);
        $this->assertTrue(is_array($actual));
        $this->assertTrue(array_key_exists('included', $actual), 'array contains a comment key');
        // $this->assertTrue(array_key_exists('author', $actual), 'array contains an author key');
    }
}