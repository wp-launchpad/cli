<?php

namespace RocketLauncherBuilder\Commands;

use Ahc\Cli\IO\Interactor;
use League\Flysystem\Filesystem;
use RocketLauncherBuilder\Entities\Configurations;
use RocketLauncherBuilder\Services\ClassGenerator;

class GenerateServiceProvider extends Command
{
    protected $name;

    protected $class_generator;

    protected $filesystem;

    protected $configurations;

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

    // This method is auto called before `self::execute()` and receives `Interactor $io` instance
    public function interact(Interactor $io)
    {
        // Collect missing opts/args
        if (!$this->name) {
            $this->set('name', $io->prompt('Enter name from the service provider'));
        }
    }

    // When app->handle() locates `init` command it automatically calls `execute()`
    // with correct $ball and $apple values
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
