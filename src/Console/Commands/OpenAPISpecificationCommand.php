<?php

namespace Chinmay\OpenApiLaravel\Console\Commands;

use Chinmay\OpenApiLaravel\Factory;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class OpenAPISpecificationCommand extends Command
{
    protected $signature = 'openapi:generate';

    protected $description = 'Generate Open API Specification schema';

    public function handle(Factory $factory): void
    {
        $json = $factory->make()->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $path = Config::get('openapi.schema.path');
        $filename = Config::get('openapi.schema.filename');

        (new Filesystem)->ensureDirectoryExists($path);

        File::put("{$path}/{$filename}", $json);
    }
}
