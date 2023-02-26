<?php

namespace RocketLauncherBuilder\Commands;

use Ahc\Cli\IO\Interactor;
use League\Flysystem\Filesystem;
use RocketLauncherBuilder\Entities\Configurations;
use RocketLauncherBuilder\Services\ClassGenerator;

class GenerateFixtureCommand extends Command
{

    protected $name;

    /**
     * @var ClassGenerator
     */
    protected $class_generator;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Configurations
     */
    protected $configurations;

    /**
     * @param ClassGenerator $class_generator
     * @param Filesystem $filesystem
     * @param Configurations $configurations
     */
    public function __construct(ClassGenerator $class_generator, Filesystem $filesystem, Configurations $configurations)
    {
        parent::__construct('fixture', 'Generate fixture class');

        $this->class_generator = $class_generator;
        $this->filesystem = $filesystem;
        $this->configurations = $configurations;

        $this
            ->argument('[name]', 'Full name from the fixture')
            // Usage examples:
            ->usage(
            // append details or explanation of given example with ` ## ` so they will be uniformly aligned when shown
                '<bold>  $0 fixture</end> <comment>MyClass </end> ## Create the fixture<eol/>'
            );
    }

    // This method is auto called before `self::execute()` and receives `Interactor $io` instance
    public function interact(Interactor $io)
    {
        // Collect missing opts/args
        if (!$this->name) {
            $this->set('name', $io->prompt('Enter name from the fixture'));
        }
    }

    // When app->handle() locates `init` command it automatically calls `execute()`
    // with correct $ball and $apple values
    public function execute($name)
    {
        $io = $this->app()->io();
        $test_namespace = str_replace('\\', '/', $this->configurations->getBaseNamespace() ) . 'Tests/';
        $fixture_path = $test_namespace . 'Fixtures/classes/' . $name;
        $relative_path = '/classes/' . $name . '.php';
        $path = $this->class_generator->generate('fixture.php.tpl', $fixture_path, [], true);

        if( ! $path ) {
            $io->write("The class already exists", true);
            return;
        }

        $bootstrap = $this->configurations->getTestDir() . '/Unit/bootstrap.php';

        $bootstrap_content = $this->filesystem->read( $bootstrap );
        preg_match('/\$fixtures = \[(?<content>[^]]*)];/', $bootstrap_content, $content);
        $content = $content['content'] . " '" . $relative_path . "',\n";
        $bootstrap_content = preg_replace('/\$fixtures = \[(?<content>[^\]])*];/', "\$fixtures = [$content           ];", $bootstrap_content);

        $this->filesystem->update( $bootstrap, $bootstrap_content );
    }
}
