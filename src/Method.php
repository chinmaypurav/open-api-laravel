<?php

namespace Chinmay\OpenApiLaravel;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionException;

class Method
{
    public ?\ReflectionParameter $requestClass = null;

    public string $docComment = '';

    /**
     * @throws ReflectionException
     */
    public function resolve(Closure|string $uses): self
    {
        if ($uses instanceof Closure) {
            $this->resolveClosure($uses);

            return $this;
        }

        $this->resolveController($uses);

        return $this;
    }

    /**
     * @throws ReflectionException
     */
    protected function resolveClosure(Closure $closure): void
    {
        $closureReflection = new \ReflectionFunction($closure);

        $parameters = Collection::make($closureReflection->getParameters());

        $this->requestClass = $parameters->firstwhere(
            fn (\ReflectionParameter $parameter) => is_subclass_of($parameter->getType()->getName(), FormRequest::class)
        );

        $this->resolveDocComment($closureReflection->getDocComment());
    }

    /**
     * @throws ReflectionException
     */
    protected function resolveController(string $controllerAction): void
    {
        $controller = Str::before($controllerAction, '@');
        $method = Str::after($controllerAction, '@');

        $class = new \ReflectionClass($controller);
        $method = $class->getMethod($method);

        $parameters = Collection::make($method->getParameters());

        $this->requestClass = $parameters->firstwhere(
            fn (\ReflectionParameter $parameter) => is_subclass_of($parameter->getType()->getName(), FormRequest::class)
        );

        $this->resolveDocComment($method->getDocComment());
    }

    protected function resolveDocComment(string|false $docComment): void
    {
        if (! $docComment) {
            return;
        }

        $this->docComment = Str::of($docComment)
            ->substr(4)
            ->beforeLast("\n")
            ->explode("\n")
            ->filter(fn (string $line) => ! Str::contains($line, ['@']))
            ->map(fn (string $line) => Str::of($line)->after('*')->trim()->toString())
            ->implode(fn (string $value) => $value, ' ');
    }
}
