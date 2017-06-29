<?php

namespace TimothyVictor\JsonAPI;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $JsonApiTransformer;

    public function __construct(Responder $responder)
    {
        $this->jsonResponder = $responder;
    }
}
