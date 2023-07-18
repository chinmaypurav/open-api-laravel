<?php

namespace Chinmay\OpenApiLaravel;

use Chinmay\OpenApi\Info;
use Chinmay\OpenApi\OpenApi;
use Chinmay\OpenApi\Operation;
use Chinmay\OpenApi\PathItem;
use Chinmay\OpenApi\Paths;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
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
            fn(Route $route, int $key) => Arr::get($route->action, 'tag', '')
        )->mapWithKeys(
            fn(Collection $routes, string $group) => $routes->map(
                fn(Route $route) => $this->makePathItem($route)
            )
        );

        return $this->paths;
    }

    protected function makePathItem(Route $route) :Route
    {
        $operation = new Operation();

        if ($tag = $route->action['tag'] ?? ''){
            $operation->setTags([$tag]);
        }

        $pathItem = new PathItem();
        $pathItem->setOperation($this->resolveMethod($route->methods), $operation);
        $this->paths->put('/'.$route->uri, $pathItem);

        return $route;
    }

    protected function resolveMethod(array $methods): string
    {
        return Str::lower($methods[0]);
    }
}