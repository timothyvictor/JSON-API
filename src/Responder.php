<?php

namespace TimothyVictor\JsonAPI;

use Illuminate\Support\Collection;
// use Illuminate\Http\Response;
// use Illuminate\Http\JsonResponse;
// use Illuminate\Http\Request;

class Responder
{
    private $response;
    private $serialize;

    public function __construct(Response $response, Serializer $serialize)
    {
        $this->response = $response;
        $this->serialize = $serialize;
    }

    public function respondWithCollection(Collection $collection)
    {
        return $this->response->respondOk($this->serialize->serializeCollection($collection));
    }

    public function respondWithResource(Transformer $item)
    {
        return $this->response->respondOk($this->serialize->serializeResource($item));
    }
}