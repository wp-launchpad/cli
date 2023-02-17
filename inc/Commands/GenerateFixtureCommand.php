<?php

namespace PSR2PluginBuilder\Commands;

use League\Flysystem\Filesystem;
use PSR2PluginBuilder\Entities\Configurations;
use PSR2PluginBuilder\Services\ClassGenerator;

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
            ->argument('<name>', 'Full name from the fixture');
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