<?php

namespace PSR2PluginBuilder\Tests\Unit\inc\Services\SetUpGenerator;

use League\Flysystem\Filesystem;
use Mockery;
use PSR2PluginBuilder\Services\SetUpGenerator;
use PSR2PluginBuilder\Templating\Renderer;
use PSR2PluginBuilder\Tests\Unit\TestCase;

/**
 * @covers \PSR2PluginBuilder\Services\SetUpGenerator::create_id
 *
 */
class Test_createId extends TestCase {

    protected $filesystem;
    protected $renderer;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filesystem = Mockery::mock(Filesystem::class);
        $this->renderer = Mockery::mock(Renderer::class);
        $this->service = new SetUpGenerator($this->filesystem, $this->renderer);
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected( $config, $expected )
    {
        $this->assertSame($expected, $this->service->create_id($config));
    }
}
