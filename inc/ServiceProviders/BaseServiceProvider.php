<?php

namespace PSR2PluginBuilder\ServiceProviders;

use Ahc\Cli\Application;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PSR2PluginBuilder\Commands\GenerateServiceProvider;
use PSR2PluginBuilder\Commands\GenerateSubscriberCommand;
use PSR2PluginBuilder\Commands\GenerateTestsCommand;
use PSR2PluginBuilder\Entities\Configurations;
use PSR2PluginBuilder\Templating\Renderer;

class BaseServiceProvider implements ServiceProviderInterface
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
    protected $configs;

    /**
     * @var string
     */
    protected $project_dir;

    public function __construct(Configurations $configs, string $project_dir)
    {
        $this->configs = $configs;
        $this->project_dir = $project_dir;
        // The internal adapter
        $adapter = new LocalFilesystemAdapter(
        // Determine root directory
            $this->project_dir
        );

        // The FilesystemOperator
        $this->filesystem = new Filesystem($adapter);

        $this->renderer = new Renderer($this->project_dir . '/templates/');
    }

    public function attach_commands(Application $app): Application
    {
        $app->add(new GenerateSubscriberCommand($this->filesystem, $this->renderer, $this->configs));
        $app->add(new GenerateServiceProvider($this->filesystem, $this->renderer, $this->configs));
        $app->add(new GenerateTestsCommand($this->filesystem, $this->renderer, $this->configs));
        return $app;
    }
}