<?php

return [
    'driver' => env('SCOUT_DRIVER', 'elastic'),

    'prefix' => env('SCOUT_PREFIX', ''),

    'queue' => env('SCOUT_QUEUE', false),

    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],

    'soft_delete' => false,

    'identify' => env('SCOUT_IDENTIFY', true),

    'elasticsearch' => [
        'index' => env('ELASTICSEARCH_INDEX', ''),
        'hosts' => [
            env('ELASTICSEARCH_HOST', 'localhost').':'.env('ELASTICSEARCH_PORT', '9200'),
        ],
    ],
];
