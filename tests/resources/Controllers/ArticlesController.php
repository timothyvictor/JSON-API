<?php

namespace TimothyVictor\JsonAPI\Test\Resources\Controllers;

use TimothyVictor\JsonAPI\Controller as JsonApiController;
use TimothyVictor\JsonAPI\Test\Resources\Models\Article;
use Illuminate\Http\Request;
// use Illuminate\Http\Response;
// use Response;

class ArticlesController extends JsonApiController
{
    public function index()
    {
        return $this->jsonResponder->respondWithCollection(Article::all());
    }

    public function show(Request $request, $id)
    {
        return $this->jsonResponder->respondWithResource(Article::find($id), $request);
    }
}