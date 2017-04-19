<?php

namespace TimothyVictor\JsonAPI;

use Illuminate\Http\JsonResponse;

class Response {

    private $headers = ['Content-Type' => 'application/vnd.api+json'];

    public function respondOk(array $data) : JsonResponse
    {
        return response()->json($data, 200, $this->headers);
    }
}