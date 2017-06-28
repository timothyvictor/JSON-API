<?php

namespace TimothyVictor\JsonAPI;

use Illuminate\Support\Collection;

class ValidateBody
{
    
    protected $apiResponse;

    public function __construct(Response $apiResponse)
    {
        $this->apiResponse = $apiResponse;
    }

    public function handle($request, \Closure $next)
    {
        // dump($request->fullUrl(), $request->isJson());
        $body = $request->all();
        if (count($body)) {
            $method = $request->method();
            if ($method == "POST" || $method == "PATCH") {
                $schema = file_get_contents(realpath(__DIR__ . '/schemas') . "/{$method}.json");
                $validator = new \JsonSchema\Validator;
                $body = json_decode(json_encode($body));
                $validator->validate($body, json_decode($schema));
                $message = [];
                if (!$validator->isValid()) {
                    return $this->respondInvalid($validator->getErrors());
                }
            }
        }
        // return $this->assertTrue($validator->isValid(), $message);
        // exit(dump(json_decode(json_encode($request->all()))));
        return $next($request);
    }

    private function respondInvalid(array $errors)
    {
        // dump($errors);
        $errorArray = collect($errors)->map(function ($error) {
            return [
                'detail' => $error['message'],
                'source' => [
                    'pointer' => $error['pointer']
                ],
                'status' => '400'
            ];
        })->toArray();
        // dump($errorArray);
        return $this->apiResponse->badRequest($errorArray);
    }
}
