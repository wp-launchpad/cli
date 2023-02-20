<?php

namespace RocketLauncherBuilder\ServiceProviders;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use RocketLauncherBuilder\App;
use RocketLauncherBuilder\Commands\GenerateFixtureCommand;
use RocketLauncherBuilder\Commands\GenerateServiceProvider;
use RocketLauncherBuilder\Commands\GenerateSubscriberCommand;
use RocketLauncherBuilder\Commands\GenerateTableCommand;
use RocketLauncherBuilder\Commands\GenerateTestsCommand;
use RocketLauncherBuilder\Entities\Configurations;
use RocketLauncherBuilder\Services\ClassGenerator;
use RocketLauncherBuilder\Services\ProviderManager;
use RocketLauncherBuilder\Services\SetUpGenerator;
use RocketLauncherBuilder\Templating\Renderer;

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

        $adapter = new Local(
        // Determine root directory
            $app_dir
        );

        // The FilesystemOperator
        $template_filesystem = new Filesystem($adapter);

        $this->renderer = new Renderer($template_filesystem, '/templates/');
    }

    public function attach_commands(App $app): App
    {
        $class_generator = new ClassGenerator($this->filesystem, $this->renderer, $this->configs);
        $provider_manager = new ProviderManager($app, $this->filesystem, $class_generator, $this->renderer);

        $setup_generator = new SetUpGenerator($this->filesystem, $this->renderer);

        $app->add(new GenerateSubscriberCommand($class_generator, $this->filesystem, $provider_manager));
        $app->add(new GenerateServiceProvider($class_generator, $this->filesystem, $this->configs));
        $app->add(new GenerateTableCommand($class_generator, $this->configs, $provider_manager));
        $app->add(new GenerateTestsCommand($class_generator, $this->configs, $this->filesystem, $setup_generator));
        $app->add(new GenerateFixtureCommand($class_generator, $this->filesystem, $this->configs));
        return $app;
    }
}
