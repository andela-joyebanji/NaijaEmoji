<?php

return [
    'settings' => [
        'displayErrorDetails' => false, // set to false in production
        'debug'               => false,
        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__.'/../templates/',
        ],
        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__.'/../logs/app.log',
        ],
    ],
];
