<?php

namespace RocketLauncherBuilder\Tests\Integration\inc\Commands\GenerateServiceProvider;

use RocketLauncherBuilder\Tests\Integration\TestCase;

class Test_Execute extends TestCase
{
    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected($config, $expected) {
        self::assertFalse($this->filesystem->exists($expected['path']));
        $this->launch_app("provider {$config['class']}");
        self::assertTrue($this->filesystem->exists($expected['path']));
        self::assertSame($expected['content'], $this->filesystem->get_contents($expected['path']));
        self::assertSame($expected['plugin_content'], $this->filesystem->get_contents($config['plugin_path']));
    }
}
