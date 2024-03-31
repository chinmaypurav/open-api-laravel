<?php

namespace Chinmay\OpenApiLaravel;

use Chinmay\OpenApiLaravel\Console\Commands\OpenAPISpecificationCommand;
use Illuminate\Support\ServiceProvider;

class OpenApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/openapi.php',
            'openapi'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
               OpenAPISpecificationCommand::class
            ]);
        }

        $this->publishes([
            __DIR__.'/../config/openapi.php' => config_path('openapi.php'),
        ], 'openapi');
    }
}
