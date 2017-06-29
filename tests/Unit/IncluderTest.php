<?php

namespace TimothyVictor\JsonAPI\Test;

use TimothyVictor\JsonAPI\Includer;
use TimothyVictor\JsonAPI\Test\Resources\Models\Article;
use TimothyVictor\JsonAPI\Test\Resources\Models\Author;
use TimothyVictor\JsonAPI\Test\Resources\Models\Category;
use TimothyVictor\JsonAPI\Test\Resources\Models\Comment;

class IncluderTest extends TestCase
{
    public function test_get_includes_returns_an_array_of_included_resources()
    {
        $article = factory(Article::class)->create();
        $comments = $article->comments()->saveMany(factory(Comment::class, 3)->create());
        $authors = factory(Author::class, 2)->create();
        $comments->each(function ($comment, $key) use ($authors) {
            $comment->author()->associate($authors->random());
            $comment->save();
        });
        $categories = $article->category()->associate(factory(Category::class)->create())->save();
        $parameters = ['includes' => [], 'fields' => []];
        $parameters['includes'] = ['category', 'comments.author'];

        $includer = $this->app->make(Includer::class);
        $actual = $includer->getIncludes($article, $parameters);

        $this->assertTrue(array_key_exists('included', $actual), 'array contains an included key');

        $included = collect($actual['included']);

        $expectedTypes = ['authors', 'categories', 'comments'];
        $actualTypes = $included->unique('type')->sortBy('type')->pluck('type')->all();
        $this->assertEquals($expectedTypes, $actualTypes, 'the included array only contains resources of the type requested');

        $actualCountOfItems = $included->count();
        $uniqueCountOfItems = $included->unique(function ($item) {
            return $item['type'].$item['id'];
        })->count();
        $this->assertEquals($actualCountOfItems, $uniqueCountOfItems, 'the included array does not contain any duplicates');
        // could also assert that the ids are correct..?

        // $this->assertTrue(array_key_exists('author', $actual), 'array contains an author key');
    }
}
