<?php

namespace RocketLauncherBuilder\Tests\Integration\inc\Commands\GenerateTestsCommand;

use RocketLauncherBuilder\Tests\Integration\TestCase;

class Test_Execute extends TestCase
{
    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected($config, $expected) {
        $this->launch_app("test {$config['class']}{$config['parameters']}");
    }
}
