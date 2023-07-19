<?php

namespace Chinmay\OpenApiLaravel;

use Chinmay\OpenApi\Info;
use Chinmay\OpenApi\OpenApi;
use Chinmay\OpenApi\Operation;
use Chinmay\OpenApi\Parameter;
use Chinmay\OpenApi\PathItem;
use Chinmay\OpenApi\Paths;
use Chinmay\OpenApi\Response;
use Chinmay\OpenApi\Responses;
use Chinmay\OpenApi\Schema;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class Factory
{
    public function __construct(protected Router $router, protected OpenApi $openApi, protected Paths $paths)
    {

    }

    public function make():OpenApi
    {
        $this->openApi
            ->setInfo($this->makeInfo())
            ->setPaths($this->makePaths());

        return $this->openApi;
    }

    protected function makeInfo():Info
    {
        $appName = Config::get('app.name', '');
        $version = '1.0.0';
        return new Info($appName, $version);
    }

    public function makePaths(): Paths
    {
        $allRoutes = Collection::make($this->router->getRoutes());

        $allRoutes->filter(
            fn(Route $route) => in_array('api', $route->action['middleware'])
        )->groupBy(
            fn(Route $route, int $key) => $route->uri
        )->mapWithKeys(
            fn(Collection $routes, string $uri) => $this->makePathItem($uri, $routes)
        );


        return $this->paths;
    }

    protected function makePathItem(string $uri, Collection $routes) :Collection
    {
        $pathItem = new PathItem();

        $routes->map(
            fn(Route $route) => $this->makeOperation($pathItem, $route)
        );

        $this->paths->put('/'.$uri, $pathItem);

        return $routes;
    }

    protected function makeOperation(PathItem $pathItem, Route $route): void
    {
        $operation = new Operation();

        if ($tag = $route->action['tag'] ?? ''){
            $operation->setTags([$tag]);
        }

        if (Str::contains($route->uri, ['{', '}'])){
            $this->makeParameter($route, $operation);
        }

        $responses = new Responses();

        $response = new Response('Default Response');
        $responses->putDefault($response);

        $operation->setResponses($responses);

        $pathItem->setOperation($this->resolveMethod($route->methods), $operation);
    }

    protected function resolveMethod(array $methods): string
    {
        return Str::lower($methods[0]);
    }

    protected function makeParameter(Route $route, Operation $operation): void
    {
        $matches = Str::matchAll('/{([^}]*)}/', $route->uri);

        $parameters = [];
        foreach ($matches as $match){
            $parameter = new Parameter($match, 'path');
            $schema = new Schema('string');
            $parameter->setSchema($schema);

            $parameters[] = $parameter;
        }

        $operation->setParameters($parameters);
    }
}