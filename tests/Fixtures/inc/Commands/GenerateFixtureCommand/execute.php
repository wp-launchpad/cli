<?php

return [
    'vfs_dir' => '/',
    'structure' => [
        'inc' => [
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
                'bootstrap.php' => file_get_contents(ROCKER_LAUNCHER_BUILDER_TESTS_FIXTURES_DIR . '/files/unit/bootstrap.php'),
            ]
        ]
    ],
    'test_data' => [
        '' => [
            'config' => [

            ],
            'expected' => [

            ]
        ]
    ]
];
