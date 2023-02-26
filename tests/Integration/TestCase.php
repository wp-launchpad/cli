<?php

namespace RocketLauncherBuilder\Tests\Integration;

use ReflectionObject;
use WPMedia\PHPUnit\Unit\TestCase as UnitTestCase;

class TestCase extends UnitTestCase
{
    protected $config;
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
