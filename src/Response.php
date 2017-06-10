<?php

namespace TimothyVictor\JsonAPI;

use Illuminate\Http\JsonResponse;

class Response {

    private $headers = ['Content-Type' => 'application/vnd.api+json'];

    private $apiMember = ['jsonapi' => [ "version" => "1.0" ]];

    private function getApiMember()
    {
        return $this->apiMember;
    }

    public function respondOk(array $data) : JsonResponse
    {
        return response()->json($data, 200, $this->headers);
    }

    public function respondUnsupportedMediaType($message = 'Clients MUST send all JSON API data in request documents with the header "Content-Type: application/vnd.api+json" without any media type parameters.')
    {
        $data = [
            'errors' => [
                [
                    'title' => 'Unsupported Media Type',
                    'detail' => $message,
                    'status' => "415",
                ]
            ],
        ];
        return response()->json(array_merge($this->getApiMember(), $data), 415, $this->headers);
    }
    public function notAcceptable($message = 'Clients that include the JSON API media type in their Accept header MUST specify the media type there at least once without any media type parameters.')
    {
        $data = [
            'errors' => [
                [
                    'title' => 'Not Acceptable',
                    'detail' => $message,
                    'status' => "406",
                ]
            ],
        ];
        return response()->json(array_merge($this->getApiMember(), $data), 406, $this->headers);
    }


}