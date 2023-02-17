<?php

namespace PSR2PluginBuilder\Tests\Unit\inc\Templating\Renderer;

use League\Flysystem\Filesystem;
use Mockery;
use PSR2PluginBuilder\Templating\Renderer;
use PSR2PluginBuilder\Tests\Unit\TestCase;

/**
 * @covers \PSR2PluginBuilder\Templating\Renderer::apply_template
 *
 */
class Test_applyTemplate extends TestCase {

    protected $renderer;

    protected $filesystem;

    protected $template_folder = 'template_folder';

    protected function set_up()
    {
        parent::set_up();
        $this->filesystem = Mockery::mock(Filesystem::class);
        $this->renderer = new Renderer($this->filesystem, $this->template_folder);
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected( $config, $expected )
    {

    }
}
