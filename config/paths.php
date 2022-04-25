<?php

return [
    'app' => [
        /**
         * Primary app namespace
         */
        'namespace' => 'LifeSpikes\\LaravelBare\\',

        /**
         * All paths resolve relative from this. Default is three
         * levels up, since usually that's the depth of a vendor
         * or monorepo package plus the config directory depth.
         */
        'base' => realpath(__DIR__ . '/../../../../'),

        /**
         * Except for core config and bootstrap files,
         * every other path can be specified here.
         */
        'paths' => [
            'base' => '',
            'public' => 'public',
            'storage' => 'storage',

            'cache' => 'storage/framework',

            'app' => 'storage/app',
            'resources' => 'storage/app',
            'database' => 'storage/app/database',
            'view' => 'storage/app/views',
        ],
    ],

    /**
     * You should leave this alone for the most part,
     * it acts as the root for core framework files like
     * the Laravel bootstrap and standard config
     */
    'kernel' => [
        'base' => realpath(__DIR__ . '/../'),

        'paths' => [
            'bootstrap' => 'bootstrap',
            'config' => 'config',
            'lang' => 'resources/lang',
        ]
    ]
];
