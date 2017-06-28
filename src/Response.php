<?php

namespace TimothyVictor\JsonAPI;

use Illuminate\Http\JsonResponse;

class Response {

    private $statusCode = 200;

    private $headers = ['Content-Type' => 'application/vnd.api+json'];

    private $apiMember = ['jsonapi' => [ "version" => "1.0" ]];

    private $errors = [
        'title' => NULL,
        'detail' => NULL,
        'status' => NULL,
    ];

    private $meta = [];

    private function setStatusCode($integer)
    {
        $this->statusCode = $integer;
        return $this;
    }

    private function setErrorStatus()
    {
        $this->errors['status'] = "{$this->getStatusCode()}";
        return $this;
    }

    private function setErrorDetail(string $detail)
    {
        $this->errors['detail'] = $detail;
        return $this;
    }

    private function setErrorTitle(string $title)
    {
        $this->errors['title'] = $title;
        return $this;
    }

    private function setAllErrors(array $errors)
    {
        $this->errors = $errors;
        return $this;
    }

    private function setHeader(array $header)
    {
        $this->headers = array_merge($this->headers, $header);
    }

    private function setMeta(array $meta)
    {
        foreach($meta as $key => $value) {
            $this->meta[$key] =  $value;
        }
        return $this;
    }

    private function getStatusCode()
    {
        return $this->statusCode;
    }

    private function getHeaders()
    {
        return $this->headers;
    }

    private function getApiMember()
    {
        return $this->apiMember;
    }

    private function getErrors()
    {
        $this->setErrorStatus();
        return [
            'errors' => [$this->errors]
        ];
    }

    private function getMultipleErrors()
    {
        return [
            'errors' => $this->errors
        ];
    }

    private function getMeta()
    {
        if (empty($this->meta)){
            return [];
        }
        return [
            'meta' => $this->meta,
        ];
    }

    private function respond($body = []) : JsonResponse
    {
        return response()->json(array_merge($this->getApiMember(),$body, $this->getMeta()), $this->getStatusCode(), $this->getHeaders());
    }

    private function respondWithErrors()
    {
        $body = $this->getErrors();
        return $this->respond($body);
    }

    private function respondWithMultipleErrors()
    {
        $body = $this->getMultipleErrors();
        return $this->respond($body);
    }

    public function ok(array $data = [])
    {
        return $this->setStatusCode(200)->respond($data);
    }

    public function accepted($message = 'Your request has been accepted, but is still being processed')
    {
        return $this->setStatusCode(202)->setMeta(['message' => $message])->respond();
    }

    public function noContent()
    {
        return response(null, 204)->withHeaders($this->getHeaders());
    }

    public function resourceCreated(array $resource, string $resource_location)
    {
        $this->setHeader(['location' => $resource_location]);
        return $this->setStatusCode(201)->respond($resource);
    }

    public function badRequest($errors)
    {
        return $this->setStatusCode(400)
            ->setAllErrors($errors)
            ->respondWithMultipleErrors();
    }

    public function notFound($message = 'The requested resource could not be found.')
    {
        return $this->setStatusCode(404)
                ->setErrorTitle('Not Found')
                ->setErrorDetail($message)
                ->respondWithErrors();
    }

    public function notAcceptable($message = 'Clients that include the JSON API media type in their Accept header MUST specify the media type there at least once without any media type parameters.')
    {
        return $this->setStatusCode(406)
                ->setErrorTitle('Not Acceptable')
                ->setErrorDetail($message)
                ->respondWithErrors();
    }

    public function unsupportedMediaType($message = 'Clients MUST send all JSON API data in request documents with the header "Content-Type: application/vnd.api+json" without any media type parameters.')
    {
        return $this->setStatusCode(415)
                ->setErrorTitle('Unsupported Media Type')
                ->setErrorDetail($message)
                ->respondWithErrors();
    }

}