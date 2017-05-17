<?php

namespace TimothyVictor\JsonAPI\Test;

use TimothyVictor\JsonAPI\Includer;
use TimothyVictor\JsonAPI\Test\Resources\Models\Category;
use TimothyVictor\JsonAPI\Test\Resources\Models\Article;
use TimothyVictor\JsonAPI\Test\Resources\Models\Author;
use TimothyVictor\JsonAPI\Test\Resources\Models\Comment;

class IncluderTest extends TestCase
{
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

        $includer = $this->app->make(Includer::class);
        $actual = $includer->getIncludes($article, $includes);

        $this->assertTrue(is_array($actual));
        $this->assertTrue(array_key_exists('included', $actual), 'array contains a comment key');
        // $this->assertTrue(array_key_exists('author', $actual), 'array contains an author key');
    }
}