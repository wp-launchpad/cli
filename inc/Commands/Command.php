<?php

namespace PSR2PluginBuilder\Commands;

use League\Flysystem\Filesystem;
use PSR2PluginBuilder\Entities\Configurations;
use PSR2PluginBuilder\Templating\Renderer;

abstract class Command extends \Ahc\Cli\Input\Command
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var Configurations
     */
    protected $configurations;

    public function __construct(string $name, string $description, Filesystem $filesystem, Renderer $renderer, Configurations $configurations)
    {
        parent::__construct($name, $description);

        $this->filesystem = $filesystem;
        $this->renderer = $renderer;
        $this->configurations = $configurations;
    }
}