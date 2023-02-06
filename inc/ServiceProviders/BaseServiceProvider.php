<?php

namespace PSR2PluginBuilder\ServiceProviders;

use Ahc\Cli\Application;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PSR2PluginBuilder\Commands\GenerateServiceProvider;
use PSR2PluginBuilder\Commands\GenerateSubscriberCommand;
use PSR2PluginBuilder\Commands\GenerateTableCommand;
use PSR2PluginBuilder\Commands\GenerateTestsCommand;
use PSR2PluginBuilder\Entities\Configurations;
use PSR2PluginBuilder\Services\ClassGenerator;
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
        $class_generator = new ClassGenerator($this->filesystem, $this->renderer, $this->configs);

        $app->add(new GenerateSubscriberCommand($class_generator));
        $app->add(new GenerateServiceProvider($class_generator));
        $app->add(new GenerateTableCommand($class_generator, $this->configs));
        $app->add(new GenerateTestsCommand($class_generator, $this->configs));
        return $app;
    }
}