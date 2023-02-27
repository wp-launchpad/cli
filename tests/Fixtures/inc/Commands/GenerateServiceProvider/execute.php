<?php

return [
    'vfs_dir' => '/',
    'structure' => [
        'inc' => [
            'Plugin.php' => file_get_contents(ROCKER_LAUNCHER_BUILDER_TESTS_FIXTURES_DIR . '/files/plugin.php'),
            'Engine' => [

            ]
        ],
        'composer.json' => file_get_contents(ROCKER_LAUNCHER_BUILDER_TESTS_FIXTURES_DIR . '/files/composer.php'),
        'tests' => [
            'Fixtures' => [
                'classes' => [

                ]
            ],
            'Integration' => [

            ],
            'Unit' => [
                'inc' => [

                ],
            ]
        ]
    ],
    'test_data' => [
        'providerShouldCreateTheFileAndAddItToPlugin' => [
            'config' => [
                'class' => 'PSR2Plugin/Engine/Test/MyProvider',
                'plugin_path' => '/inc/Plugin.php'
            ],
            'expected' => [
                'path' => '/inc/Engine/Test/MyProvider.php',
                'content' => file_get_contents(__DIR__ . '/files/provider.php'),
                'plugin_content' => file_get_contents(__DIR__ . '/files/plugin.php'),
            ]
        ]
    ]
];
