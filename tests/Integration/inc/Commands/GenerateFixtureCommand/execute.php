<?php

namespace RocketLauncherBuilder\Tests\Integration\inc\Commands\GenerateFixtureCommand;

use RocketLauncherBuilder\Tests\Integration\TestCase;

class Test_Execute extends TestCase
{
    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected($config, $expected) {
            $this->launch_app('fixture Test');
    }
}
