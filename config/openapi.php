<?php

return [
    'schema' => [
        'path' => storage_path('openapi'),
        'filename' => 'schema.json',
    ],
    'routes' => [
        'include' => [
            'middlewares' => ['api'],
            'paths' => []
        ],
        'exclude' => [
            'middlewares' => [],
            'paths' => []
        ],
    ],
];