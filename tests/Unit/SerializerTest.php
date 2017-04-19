<?php

namespace TimothyVictor\JsonAPI\Test;

use TimothyVictor\JsonAPI\Serializer;
use TimothyVictor\JsonAPI\Test\Resources\Models\Category;

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
    
}