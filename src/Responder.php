<?php

namespace TimothyVictor\JsonAPI;

use Illuminate\Support\Collection;
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

    public function respondWithCollection(Collection $collection)
    {
        return $this->response->respondOk($this->assemble->assembleCollection($collection));
    }

    public function include_to_array($include_string)
    {
        // $includes = Helper::comma_to_array($include_string);
        // $array = [];
        // foreach ($includes as $include){
        //     $array[] = Helper::dot_to_array($include);
        // }
        // return $array;
        return Helper::comma_to_array($include_string);
    }

    public function respondWithResource(Transformer $item, $request = null)
    {
        $includes = [];
        if (isset($request)){
            $include = $request->input('include');
        }
        if(isset($include)){
            $includes = $this->include_to_array($include);
            // dd($includes);
        }
        // dd($includes);
        return $this->response->respondOk($this->assemble->assembleResource($item, $includes));
    }
}