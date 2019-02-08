<?php

return [
    'cache' => [
        'enabled' => env('REPOSITORY_CACHE_ENABLED', false),

        'duration' => env('REPOSITORY_CACHE_MINUTES', 30),
    ],
];
