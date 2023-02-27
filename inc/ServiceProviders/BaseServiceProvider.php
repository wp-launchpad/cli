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
use RocketLauncherBuilder\Services\BootstrapManager;
use RocketLauncherBuilder\Services\ClassGenerator;
use RocketLauncherBuilder\Services\ContentGenerator;
use RocketLauncherBuilder\Services\FixtureGenerator;
use RocketLauncherBuilder\Services\ProjectManager;
use RocketLauncherBuilder\Services\ProviderManager;
use RocketLauncherBuilder\Services\SetUpGenerator;
use RocketLauncherBuilder\Templating\Renderer;

class BaseServiceProvider implements ServiceProviderInterface
{

    /**
     * Interacts with the filesystem.
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Renderer that handles layout of template files.
     *
     * @var Renderer
     */
    protected $renderer;

    /**
     * Configuration from the project.
     *
     * @var Configurations
     */
    protected $configs;

    /**
     * Base directory from the project.
     *
     * @var string
     */
    protected $project_dir;

    /**
     * Instantiate the class.
     *
     * @param Configurations $configs configuration from the project.
     * @param Filesystem $filesystem Interacts with the filesystem.
     * @param string $app_dir base directory from the cli.
     */
    public function __construct(Configurations $configs, Filesystem $filesystem, string $app_dir)
    {
        $this->configs = $configs;
        $this->filesystem = $filesystem;

        $adapter = new Local(
        // Determine root directory
            $app_dir
        );

        // The FilesystemOperator
        $template_filesystem = new Filesystem($adapter);

        $this->renderer = new Renderer($template_filesystem, '/templates/');
    }

    /**
     * Attach commands from the service provider to the cli.
     *
     * @param App $app cli.
     *
     * @return App
     */
    public function attach_commands(App $app): App
    {
        $class_generator = new ClassGenerator($this->filesystem, $this->renderer, $this->configs);
        $provider_manager = new ProviderManager($app, $this->filesystem, $class_generator, $this->renderer);

        $setup_generator = new SetUpGenerator($this->filesystem, $this->renderer, $this->configs);

        $fixture_generator =  new FixtureGenerator($this->filesystem, $this->renderer);

        $content_generator = new ContentGenerator($this->filesystem, $this->renderer);

        $project_manager = new ProjectManager($this->filesystem);

        $bootstrap_manager = new BootstrapManager($this->filesystem, $this->configs, $this->renderer);

        $app->add(new GenerateSubscriberCommand($class_generator, $this->filesystem, $provider_manager));
        $app->add(new GenerateServiceProvider($class_generator, $this->filesystem, $this->configs));
        $app->add(new GenerateTableCommand($class_generator, $this->configs, $provider_manager));
        $app->add(new GenerateTestsCommand($class_generator, $this->configs, $this->filesystem, $setup_generator, $fixture_generator, $content_generator, $bootstrap_manager, $project_manager));
        $app->add(new GenerateFixtureCommand($class_generator, $this->filesystem, $this->configs));

        return $app;
    }
}
