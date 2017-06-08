<?php

namespace TimothyVictor\JsonAPI\Test\Resources\Controllers;

use TimothyVictor\JsonAPI\Controller as JsonApiController;
use TimothyVictor\JsonAPI\Test\Resources\Models\Category;
use Illuminate\Http\Request;

// use Illuminate\Http\Response;
// use Response;

class CategoriesController extends JsonApiController
{
    public function index(Request $request)
    {
        // $Category::paginate(2)->items()));
        return $this->jsonResponder->respondWithCollection(Category::all(), $request);
    }

    public function show(Request $request, $id)
    {
        return $this->jsonResponder->respondWithResource(Category::find($id), $request);
    }
}
