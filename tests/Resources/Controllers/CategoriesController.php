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

    public function store(Request $request)
    {
        $category = Category::create($request->input('data.attributes'));
        return $this->jsonResponder->respondResourceCreated($category, $request);
    }

    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        $category->update($request->input('data.attributes'));
        return $this->jsonResponder->respondWithResource($category, $request);
    }
}
