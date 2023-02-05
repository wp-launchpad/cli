<?php

namespace PSR2PluginBuilder\Commands;

use League\Flysystem\Filesystem;
use PSR2PluginBuilder\Entities\Configurations;
use PSR2PluginBuilder\Templating\Renderer;

class GenerateServiceProvider extends Command
{
    protected $name;

    public function __construct(Filesystem $filesystem, Renderer $renderer, Configurations $configurations)
    {
        parent::__construct('provider', 'Generate service provider class', $filesystem, $renderer, $configurations);

        $this
            ->argument('<name>', 'Full name from the service provider')
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
        $basename = basename( $name );
        $namespace = implode('\\', array_slice(explode('/', $name), 0, -1));
        $io = $this->app()->io();

        $base_namespace = str_replace('\\', '/', $this->configurations->getBaseNamespace());

        $path = str_replace(
                [$base_namespace, "/"],
                [$this->configurations->getCodeDir(), DIRECTORY_SEPARATOR],
                $name
            ) . '.php';

        if( $this->filesystem->fileExists($path)) {
            $io->write("The class already exists", true);
            return;
        }

        $content = $this->renderer->apply_template('serviceprovider.php.tpl', [
            'namespace' => $namespace,
            'base_namespace' => $this->configurations->getBaseNamespace(),
            'class_name' => $basename
        ]);

        $this->filesystem->write($path, $content);

        $io->write("The service provider is created at this path: $path", true);
    }
}