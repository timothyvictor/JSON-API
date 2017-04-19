<?php

namespace TimothyVictor\JsonAPI;

use Illuminate\Support\ServiceProvider;

class JsonApiServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        // $this->app->singleton(Transformer::class, function ($app) {
        //     return new Transformer;
        // });
        // $this->app->singleton(Responder::class, function ($app) {
        //     return new Responder($app->make(Transformer::class));
        // });
    }
}