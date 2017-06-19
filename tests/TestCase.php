<?php

namespace TimothyVictor\JsonAPI\Test;

use Orchestra\Testbench\Exceptions\Handler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Exception;;
use Illuminate\Http\Response;
use Route;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected $topLevelMembers = ['jsonapi', 'data'];
    protected $resourceMembers = ['id', 'type', 'attributes'];
    private $contentType = 'application/vnd.api+json';


    protected function getContentTypeHeader()
    {
        return ['Content-Type' => $this->contentType];
    }
    protected function getAcceptHeader()
    {
        return ['Accept' => $this->contentType];
    }
    protected function getHeaders()
    {
        return array_merge($this->getContentTypeHeader(), $this->getAcceptHeader());
    }

    public function setUp()
    {
        parent::setUp();
        $this->app->make('Illuminate\Contracts\Http\Kernel')
            ->pushMiddleware('\TimothyVictor\JsonAPI\ValidateHeaders::class');
            // ->pushMiddleware('\TimothyVictor\JsonAPI\ValidateBody::class');
        // $this->initializeDirectory($this->getTempDirectory());
        // $this->setUpDatabase($this->app);
        $this->setUpRoutes($this->app);
        $this->withFactories(__DIR__.'/resources/factories');
        // $this->setUpMiddleware();
        $this->loadMigrationsFrom(realpath(__DIR__.'/Resources/migrations'));
        // $this->loadMigrationsFrom([
        //     '--database' => 'testing',
        //     '--realpath' => realpath(__DIR__.'/../migrations'),
        // ]);
    }

    protected function arrays_have_same_values(array $array1, array $array2) : bool
    {
        return empty(array_diff($array1, $array2));
    }

    protected function setUpRoutes($app)
    {
        require(__DIR__ . '/resources/routes/api.php');
        
    }

    protected function getPackageProviders($app)
    {
        return [
            // \TimothyVictor\JsonAPI\JsonApiServiceProvider::class,
            \Orchestra\Database\ConsoleServiceProvider::class,
        ];
    }


    protected function disableExceptionHandling()
    {
        $this->app->instance(ExceptionHandler::class, new class extends Handler {
            public function __construct() {}
            public function report(Exception $e) {}
            public function render($request, Exception $e) {
                throw $e;
            }
        });
    }

    protected function assertValidJsonApiStructure($data, $definition = 'schema')
    {

        $schema = file_get_contents(realpath(__DIR__.'/resources/schemas') . "/{$definition}.json");
        $validator = new \JsonSchema\Validator;
        $validator->validate($data, json_decode($schema));
        $message = "";
        if(!$validator->isValid()){
            $message = "JSON does not validate. Violations:\n";
            // dump($validator->getErrors());
            foreach ($validator->getErrors() as $error) {
                $message .= "[{$error['property']}] {$error['message']}\n";
            }
        }
        return $this->assertTrue($validator->isValid(), $message);
    }
}