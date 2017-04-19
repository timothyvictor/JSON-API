<?php

namespace TimothyVictor\JsonAPI\Test\Resources\Controllers;

use TimothyVictor\JsonAPI\Controller as JsonApiController;
use TimothyVictor\JsonAPI\Test\Resources\Models\Category;
// use Illuminate\Http\Request;
// use Illuminate\Http\Response;
// use Response;

class CategoriesController extends JsonApiController
{
    public function index()
    {
        return $this->jsonResponder->respondWithCollection(Category::all());
        // return $this->JsonApiSerializer->transformCollection(Category::all());
    }

    public function show($id)
    {
        return $this->jsonResponder->respondWithResource(Category::find($id));
    }
}