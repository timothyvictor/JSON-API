<?php

namespace TimothyVictor\JsonAPI\Test;

use TimothyVictor\JsonAPI\Test\Resources\Models\Category;

class ModelTest extends TestCase
{
    public function test_transform_id_returns_the_models_key()
    {
        $category = factory(Category::class)->create();
        $this->assertEquals($category->transformId(), $category->getRouteKey());
    }

    public function test_transform_type_returns_the_plural_of_the_models_class_name()
    {
        $category = factory(Category::class)->create();
        $this->assertTrue($category->transformType() === 'categories');
    }

    public function test_transform_attributes_returns_an_array()
    {
        $category = factory(Category::class)->create();
        $attributesArray = $category->transformAttributes();
        $this->assertTrue(is_array($attributesArray));
        // test that id and type are not key in the array
        $this->assertFalse(in_array('id', array_keys($attributesArray)));
        $this->assertFalse(in_array('type', array_keys($attributesArray)));
    }
}
