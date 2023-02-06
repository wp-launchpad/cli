<?php

namespace PSR2PluginBuilder\Commands;

use Ahc\Cli\IO\Interactor;
use League\Flysystem\Filesystem;
use PSR2PluginBuilder\Entities\Configurations;
use PSR2PluginBuilder\Services\ClassGenerator;
use PSR2PluginBuilder\Templating\Renderer;

class GenerateSubscriberCommand extends Command
{
    protected $name;

    /**
     * @var ClassGenerator
     */
    protected $class_generator;

    public function __construct(ClassGenerator $class_generator)
    {
        parent::__construct('subscriber', 'Generate subscriber class');

        $this->class_generator = $class_generator;

        $this
            ->argument('<name>', 'Full name from the subscriber')
            ->option('-t --type', 'Type of subscriber')
            // Usage examples:
            ->usage(
            // append details or explanation of given example with ` ## ` so they will be uniformly aligned when shown
                '<bold>  $0</end> <comment>--type common --ball ballon <arggg></end> ## details 1<eol/>' .
                // $0 will be interpolated to actual command name
                '<bold>  $0</end> <comment>-a applet -b ballon <arggg> [arg2]</end> ## details 2<eol/>'
            );
    }

    // When app->handle() locates `init` command it automatically calls `execute()`
    // with correct $ball and $apple values
    public function execute($name)
    {
        $io = $this->app()->io();

        $path = $this->class_generator->generate('subscriber.php.tpl', $name);

        if( ! $path ) {
            $io->write("The class already exists", true);
            return;
        }

        $io->write("The subscriber is created at this path: $path", true);
    }
}