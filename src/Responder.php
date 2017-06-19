<?php

namespace TimothyVictor\JsonAPI;

use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
// use Illuminate\Http\Response;
// use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Responder
{
    private $response;
    private $assemble;

    public function __construct(Response $response, Assembler $assembler)
    {
        $this->response = $response;
        $this->assemble = $assembler;
    }

    public function respondWithCollection($collection, $request = null, $paginate = null)
    {
        if($collection instanceof LengthAwarePaginator || $collection instanceof Collection){
            $parameters = $this->getParameters($request);
            if($collection instanceof LengthAwarePaginator){
                $parameters = $this->addPaginationToParameters($collection, $parameters);
                $collection = $collection->getCollection();
                // exit(dump($collection));
            }
            return $this->response->ok($this->assemble->assembleCollection($collection, $parameters));
        } else {
            $argument = gettype($collection) == "object" ? get_class($collection) : gettype($collection);
            throw new \TypeError("Argument 1 passed to " . __METHOD__ . " must be of type Illuminate\Support\Collection or Illuminate\Pagination\LengthAwarePaginator. {$argument} given");
        }
        
    }

    private function addPaginationToParameters(LengthAwarePaginator $paginator, $parameters)
    {
        $parameters['pagination'] = [
            // 'self' => $paginator->url($paginator->currentPage()),
            'first' => $paginator->url(1),
            'last' => $paginator->url($paginator->lastPage()),
            'prev' => $paginator->previousPageUrl(),
            'next' => $paginator->nextPageUrl(),
        ];
        return $parameters;
    }

    private function getParameters($request = null)
    {
        $parameters = ['includes' => [], 'fields' => [], 'sort' => [], 'pagination' => []];
        if (isset($request)) {
            $parameters['fields'] = $request->input('fields') ? $request->input('fields') : [];
            $parameters['includes'] = $request->input('include') ? Helper::comma_to_array($request->input('include')) : [];
            $parameters['sort'] = $request->input('sort') ? Helper::dot_to_array($request->input('sort')) : [];
        }
        return $parameters;
    }

    public function respondWithResource(Transformer $item, $request = null)
    {
        $parameters = $this->getParameters($request);
        return $this->response->ok($this->assemble->assembleResource($item, $parameters));
    }

    public function respondResourceCreated(Transformer $resource, $request = null)
    {
        return $this->response->resourceCreated($this->assemble->assembleResource($resource, $this->getParameters($request)), $resource->transformSelfLink() . "/{$resource->id}");
    }
}
