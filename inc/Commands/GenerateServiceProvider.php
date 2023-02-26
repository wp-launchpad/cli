<?php

namespace RocketLauncherBuilder\Commands;

use Ahc\Cli\IO\Interactor;
use League\Flysystem\Filesystem;
use RocketLauncherBuilder\Entities\Configurations;
use RocketLauncherBuilder\Services\ClassGenerator;

class GenerateServiceProvider extends Command
{
    /**
     * Name from the service provider.
     *
     * @var string
     */
    protected $name;

    /**
     * Class generator.
     *
     * @var ClassGenerator
     */
    protected $class_generator;

    /**
     * Interacts with the filesystem.
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Configuration from the project.
     *
     * @var Configurations
     */
    protected $configurations;

    /**
     * Instantiate the class.
     *
     * @param ClassGenerator $class_generator Class generator.
     * @param Filesystem $filesystem Interacts with the filesystem.
     * @param Configurations $configurations Configuration from the project.
     */
    public function __construct(ClassGenerator $class_generator, Filesystem $filesystem, Configurations $configurations)
    {
        parent::__construct('provider', 'Generate service provider class');

        $this->filesystem = $filesystem;
        $this->configurations = $configurations;
        $this->class_generator = $class_generator;

        $this
            ->argument('[name]', 'Full name from the service provider')
            // Usage examples:
            ->usage(
            // append details or explanation of given example with ` ## ` so they will be uniformly aligned when shown
                '<bold>  $0 provider</end> <comment>MyClass</end> ## Creates the service provider<eol/>'
            );
    }

    /**
     * Interacts with the user to get missing values.
     *
     * @param Interactor $io Interface to interact with the user.
     * @return void
     */
    public function interact(Interactor $io)
    {
        // Collect missing opts/args
        if (!$this->name) {
            $this->set('name', $io->prompt('Enter name from the service provider'));
        }
    }

    /**
     * Execute the command.
     * @param string|null $name Name from the service provider.
     * @return void
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function execute($name)
    {
        $io = $this->app()->io();

        $path = $this->class_generator->generate('serviceprovider.php.tpl', $name );

        if( ! $path ) {
            $io->write("The class already exists", true);
            return;
        }

        $io->write("The service provider is created at this path: $path", true);

        $plugin_path = $this->configurations->getCodeDir() . 'Plugin.php';

        $plugin_content = $this->filesystem->read($plugin_path);

         preg_match('/\$providers = \[(?<content>[^]]*)];/', $plugin_content, $content);
        $content = $content['content'] . " " . $this->class_generator->get_fullname($name) . "::class,\n";
        $plugin_content = preg_replace('/\$providers = \[(?<content>[^\]])*];/', "\$providers = [$content           ];", $plugin_content);

        if(! $plugin_content) {
            return;
        }

        $this->filesystem->update($plugin_path, $plugin_content);
    }
}
