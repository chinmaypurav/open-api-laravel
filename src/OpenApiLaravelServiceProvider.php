<?php

namespace Chinmay\OpenApiLaravel;

use Chinmay\OpenApiLaravel\Console\Commands\OpenAPISpecificationCommand;
use Illuminate\Support\ServiceProvider;

class OpenApiLaravelServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
               OpenAPISpecificationCommand::class
            ]);
        }
    }
}
