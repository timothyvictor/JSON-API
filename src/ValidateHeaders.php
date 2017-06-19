<?php

namespace TimothyVictor\JsonAPI;

use Illuminate\Support\Collection;

class ValidateHeaders {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    
    protected $apiResponse;

    public function __construct(Response $apiResponse)
    {
        $this->apiResponse = $apiResponse;
    }

    public function handle($request, \Closure $next)
    {
        // exit(dump($request->header()));
        if ($request->header('content-type') != "application/vnd.api+json") {
            return $this->apiResponse->unsupportedMediaType();
        }
        if (!empty($request->header('accept'))) {
            $jsonApiAcceptHeader = 0;
            $hasParams = 0;
            foreach($request->header()['accept'] as $contentType){
                if(strpos($contentType, 'application/vnd.api+json') !== false){
                    $jsonApiAcceptHeader++;
                    if(strpos($contentType, ';') !== false){
                        $hasParams++;
                    }
                }
            }
            if ($jsonApiAcceptHeader <= $hasParams){
                return $this->apiResponse->notAcceptable();
            }
        }
        return $next($request);
    }
}