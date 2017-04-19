<?php

namespace TimothyVictor\JsonAPI;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $JsonApiTransformer;

    public function __construct(Responder $responder)
    {
        $this->jsonResponder = $responder;
    }
}