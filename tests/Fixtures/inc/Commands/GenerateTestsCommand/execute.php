<?php
return [
    'vfs_dir' => '/',
    'structure' => [
        'inc' => [
            'Plugin.php' => file_get_contents(ROCKER_LAUNCHER_BUILDER_TESTS_FIXTURES_DIR . '/files/plugin.php'),
            'Engine' => [
                'Test' => [
                    'MyClass' => file_get_contents(ROCKER_LAUNCHER_BUILDER_TESTS_FIXTURES_DIR . '/files/my_class.php'),
                ]
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
        'onMethodShouldCreateTestOnlyForThatMethod' => [
            'config' => [
                'class' => '',
                'parameters' => ''
            ],
            'expected' => [

            ]
        ],
        'onClassShouldCreateForAllPublicMethods' => [
            'config' => [

            ],
            'expected' => [

            ]
        ],
        'onClassWithBothParamShouldCreateBothTests' => [
            'config' => [

            ],
            'expected' => [

            ]
        ],
        'onClassWithBothShortParamShouldCreateBothTests' => [
            'config' => [

            ],
            'expected' => [

            ]
        ],
        'onClassWithUnitParamShouldCreateUnitTests' => [
            'config' => [

            ],
            'expected' => [

            ]
        ],
        'onClassWithUnitShortParamShouldCreateUnitTests' => [
            'config' => [

            ],
            'expected' => [

            ]
        ],
        'onClassWithIntegrationParamShouldCreateIntegrationTests' => [
            'config' => [

            ],
            'expected' => [

            ]
        ],
        'onClassWithIntegrationShortParamShouldCreateIntegrationTests' => [
            'config' => [

            ],
            'expected' => [

            ]
        ],
        'onClassWithGroupParamShouldAddGroup' => [
            'config' => [

            ],
            'expected' => [

            ]
        ],
        'onClassWithGroupShortParamShouldAddGroup' => [
            'config' => [

            ],
            'expected' => [

            ]
        ],
        'onClassWithExpectedPresentParamShouldAddExpected' => [
            'config' => [

            ],
            'expected' => [

            ]
        ],
        'onClassWithExpectedPresentShortParamShouldAddExpected' => [
            'config' => [

            ],
            'expected' => [

            ]
        ],
        'onClassWithExpectedAbsentParamShouldNotAddExpected' => [
            'config' => [

            ],
            'expected' => [

            ]
        ],
        'onClassWithExpectedAbsentShortParamShouldNotAddExpected' => [
            'config' => [

            ],
            'expected' => [

            ]
        ],
        'onClassWithAddScenariosShortParamShouldAddScenarios' => [
            'config' => [

            ],
            'expected' => [

            ]
        ],
        'onClassWithAddScenariosParamShouldAddScenarios' => [
            'config' => [

            ],
            'expected' => [

            ]
        ],
        'onClassWithExternalRunParamShouldCreateExternalRun' => [
            'config' => [

            ],
            'expected' => [

            ]
        ],
        'onClassWithExternalRunShortParamShouldCreateExternalRun' => [
            'config' => [

            ],
            'expected' => [

            ]
        ],
    ]
];
