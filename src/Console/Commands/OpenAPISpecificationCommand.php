<?php

namespace Chinmay\OpenApiLaravel\Console\Commands;

use Chinmay\OpenApiLaravel\Factory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class OpenAPISpecificationCommand extends Command
{
    protected $signature = 'openapi:generate';

    protected $description = 'Generated Open API Specification';

    public function handle(Factory $factory):void
    {
        $json = $factory->make()->toJson(JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);

        File::put(storage_path('open-api.json'), $json);
    }
}