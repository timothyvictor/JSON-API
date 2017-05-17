<?php

namespace TimothyVictor\JsonAPI\Test;

use TimothyVictor\JsonAPI\Assembler;
use TimothyVictor\JsonAPI\Test\Resources\Models\Category;
use TimothyVictor\JsonAPI\Test\Resources\Models\Article;
use TimothyVictor\JsonAPI\Test\Resources\Models\Author;
use TimothyVictor\JsonAPI\Test\Resources\Models\Comment;

class AssemblerTest extends TestCase
{
    public function test_assemble_collection_returns_a_valid_json_api_strutcure()
    {
        $collection = factory(Category::class, 5)->create();
        $assembler = $this->app->make(Assembler::class);

        $assembled = $assembler->assembleCollection($collection);

        $this->assertTrue($this->arrays_have_same_values($this->topLevelMembers, array_keys($assembled)));
        $this->assertTrue(is_array($assembled['data'][0]));

        $resources = collect($assembled['data']);
        $resources->each(function ($item, $key) {
            $this->assertTrue($this->arrays_have_same_values($this->resourceMembers, array_keys($item)));
        });
    }

    public function test_assemble_resource_returns_a_valid_json_api_strutcure()
    {
        $item = factory(Category::class)->create();
        $assembler = $this->app->make(Assembler::class);

        $assembled = $assembler->assembleResource($item);

        $this->assertTrue($this->arrays_have_same_values($this->topLevelMembers, array_keys($assembled)));

        $this->assertTrue($this->arrays_have_same_values($this->resourceMembers, array_keys($assembled['data'])));
    }
}