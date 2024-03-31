<?php

namespace Chinmay\OpenApiLaravel\Console\Commands;

use Chinmay\OpenApiLaravel\Factory;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;

class OpenAPISpecificationCommand extends Command
{
    protected $signature = 'openapi:generate';

    protected $description = 'Generate Open API Specification schema';

    public function handle(Factory $factory): void
    {
        $json = $factory->make()->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        (new Filesystem())->ensureDirectoryExists(storage_path('openapi'));

        File::put(storage_path('openapi/schema.json'), $json);
    }
}
