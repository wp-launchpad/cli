<?php

namespace RocketLauncherBuilder\Tests\Integration;

use ReflectionObject;
use WPMedia\PHPUnit\Unit\TestCase as UnitTestCase;
use WPMedia\PHPUnit\VirtualFilesystemTestTrait;

class TestCase extends UnitTestCase
{
    use VirtualFilesystemTestTrait;
    protected $config;

    /**
     * Path to the Fixtures directory.
     *
     * @var string
     */
    protected static $path_to_fixtures_dir;

    /**
     * Path to the config and test data in the Fixtures directory.
     * Set this path in each test class.
     *
     * @var string
     */
    protected $path_to_test_data;

    /**
     * The root virtual directory name.
     *
     * @var string
     */
    protected $rootVirtualDir = 'public';

    /**
     * Virtual directory and file permissions.
     *
     * @var int
     */
    protected $permissions = 0777;

    /**
     * Gets the default virtual directory filesystem structure.
     *
     * @return array default structure.
     */
    public function getDefaultVfs() {
        return [
            'Tests' => [
                'Integration' => [],
                'Unit'        => [],
            ],
        ];
    }

    public function configTestData() {
        if ( empty( $this->config ) ) {
            $this->loadTestDataConfig();
        }

        return isset( $this->config['test_data'] )
            ? $this->config['test_data']
            : $this->config;
    }

    protected function loadTestDataConfig() {
        $obj      = new ReflectionObject( $this );
        $filename = $obj->getFileName();

        $this->config = $this->getTestData( dirname( $filename ), basename( $filename, '.php' ) );
    }
}
