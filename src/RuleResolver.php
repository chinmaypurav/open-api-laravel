<?php

namespace Chinmay\OpenApiLaravel;

use Chinmay\OpenApi\MediaType;
use Chinmay\OpenApi\RequestBody;
use Chinmay\OpenApi\Schema;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionException;

class RuleResolver
{
    public function resolve(Closure|string $uses)
    {
//        if ($uses instanceof Closure){
//            return $this->resolveClosure($uses);
//        }

        return $this->resolveController($uses);
    }

    protected function resolveClosure(Closure $closure)
    {

    }

    /**
     * @throws ReflectionException
     */
    protected function resolveController(string $controllerAction)
    {
        $controller = Str::before($controllerAction, '@');
        $method = Str::after($controllerAction, '@');

        $class = new \ReflectionClass($controller);
        $method = $class->getMethod($method);

        $parameters =Collection::make($method->getParameters());

        $requestClass = $parameters->where(
            fn (\ReflectionParameter $parameter) => is_subclass_of($parameter->getType()->getName(), FormRequest::class)
        )->first();

        if (!$requestClass){
            return;
        }

        $class = $requestClass->getType()->getName();

        $mediaType = new MediaType();

        $rules = (new $class)->rules();

        $schema = new Schema('object');

        $rules = Collection::make($rules);

        $properties = Collection::make();

        foreach ($rules as $attribute => $rule){
            foreach ($rule as $r){
                if ($r === 'required'){
                    $schema->putRequired($attribute);
                }
            }
            $properties->put($attribute, ['type' => 'string']);

        }

        $schema->setProperties($properties->toArray());

        $mediaType->setSchema($schema);

        return (new RequestBody($mediaType));
    }
}