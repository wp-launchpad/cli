<?php

namespace PSR2PluginBuilder\ServiceProviders;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use PSR2PluginBuilder\App;
use PSR2PluginBuilder\Commands\GenerateFixtureCommand;
use PSR2PluginBuilder\Commands\GenerateServiceProvider;
use PSR2PluginBuilder\Commands\GenerateSubscriberCommand;
use PSR2PluginBuilder\Commands\GenerateTableCommand;
use PSR2PluginBuilder\Commands\GenerateTestsCommand;
use PSR2PluginBuilder\Entities\Configurations;
use PSR2PluginBuilder\Services\ClassGenerator;
use PSR2PluginBuilder\Services\ProviderManager;
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

    public function __construct(Configurations $configs, string $project_dir, string $app_dir)
    {
        $this->configs = $configs;
        $this->project_dir = $project_dir;
        // The internal adapter
        $adapter = new Local(
        // Determine root directory
            $this->project_dir
        );

        // The FilesystemOperator
        $this->filesystem = new Filesystem($adapter);

        $this->renderer = new Renderer($app_dir . '/templates/');
    }

    public function attach_commands(App $app): App
    {
        $class_generator = new ClassGenerator($this->filesystem, $this->renderer, $this->configs);
        $provider_manager = new ProviderManager($app, $this->filesystem, $class_generator, $this->renderer);

        $app->add(new GenerateSubscriberCommand($class_generator, $this->filesystem, $provider_manager));
        $app->add(new GenerateServiceProvider($class_generator, $this->filesystem, $this->configs));
        $app->add(new GenerateTableCommand($class_generator, $this->configs, $provider_manager));
        $app->add(new GenerateTestsCommand($class_generator, $this->configs));
        $app->add(new GenerateFixtureCommand($class_generator, $this->filesystem, $this->configs));
        return $app;
    }
}
