<?php

namespace Chinmay\OpenApiLaravel;

use Chinmay\OpenApi\Info;
use Chinmay\OpenApi\License;
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
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionException;

class Factory
{
    public function __construct(protected Router $router, protected OpenApi $openApi, protected Paths $paths)
    {
    }

    public function make(): self
    {
        $this->openApi
            ->setInfo($this->makeInfo())
            ->setPaths($this->makePaths());

        return $this;
    }

    public function toJson($options = 0): bool|string
    {
        return $this->openApi->toJson($options);
    }

    protected function makeInfo(): Info
    {
        $appName = Config::get('app.name', '');
        $version = '1.0.0';

        $info = new Info($appName, $version);

        $this->makeLicence($info);

        return $info;
    }

    protected function makeLicence(Info $info): void
    {
        if (! $licence = File::get(__DIR__.'/../LICENSE')) {
            return;
        }

        $licence = Str::before($licence, "\n");

        $licence = new License($licence);

        $info->setLicense($licence);
    }


    public function makePaths(): Paths
    {
        $allRoutes = Collection::make($this->router->getRoutes());

        $allRoutes->filter(
            fn (Route $route) => $this->filterPaths($route)
        )->groupBy(
            fn (Route $route, int $key) => $route->uri
        )->mapWithKeys(
            fn (Collection $routes, string $uri) => $this->makePathItem($uri, $routes)
        );


        return $this->paths;
    }

    protected function filterPaths(Route $route): bool
    {
        if ($excludedMiddlewares = config('openapi.routes.exclude.middlewares')) {

            return ! Str::startsWith($route->uri, $excludedMiddlewares);
        }

        if ($excludedPaths = config('openapi.routes.exclude.paths')) {

            return ! Str::startsWith($route->uri, $excludedPaths);
        }

        if ($includedMiddlewares = config('openapi.routes.include.middlewares')) {

            return (bool) array_intersect($route->action['middleware'], $includedMiddlewares);
        }

        if ($includedPaths = config('openapi.routes.include.paths')) {

            return ! array_intersect($route->action['middleware'], $includedPaths);
        }

        return true;
    }

    protected function makePathItem(string $uri, Collection $routes): Collection
    {
        $pathItem = new PathItem();

        $routes->map(
            fn (Route $route) => $this->makeOperation($pathItem, $route)
        );

        $this->paths->put('/'.$uri, $pathItem);

        return $routes;
    }

    /**
     * @throws ReflectionException
     */
    protected function makeOperation(PathItem $pathItem, Route $route): void
    {
        $operation = new Operation();

        if ($tag = $route->action['tag'] ?? '') {
            $operation->setTags([$tag]);
        }

        if (Str::contains($route->uri, ['{', '}'])) {
            $this->makeParameter($route, $operation);
        }

        $responses = new Responses();

        $response = new Response('Default Response');
        $responses->putDefault($response);

        $operation->setResponses($responses);

        $method = App::make(Method::class)->resolve($route->action['uses']);

        $ruleResolver = App::make(RuleResolver::class);

        if ($requestBody = $method->requestClass) {
            $operation->setRequestBody($ruleResolver->resolveBody($requestBody));
            $operation->setDescription($method->docComment);
        }

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
        foreach ($matches as $match) {
            $parameter = new Parameter($match, 'path');
            $schema = new Schema('string');
            $parameter->setSchema($schema);

            $parameters[] = $parameter;
        }

        $operation->setParameters($parameters);
    }
}
