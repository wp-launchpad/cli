<?php

namespace PSR2PluginBuilder\Commands;

use League\Flysystem\Filesystem;
use PSR2PluginBuilder\Services\ClassGenerator;

class GenerateSubscriberCommand extends Command
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

    public function __construct(ClassGenerator $class_generator, Filesystem $filesystem)
    {
        parent::__construct('subscriber', 'Generate subscriber class');

        $this->class_generator = $class_generator;
        $this->filesystem = $filesystem;

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

        $service_provider_name = $this->class_generator->get_dirname( $name ) . '/ServiceProvider';
        $service_provider_path = $this->class_generator->generate_path( $service_provider_name );

        if( ! $this->class_generator->exists( $service_provider_name ) ) {
            $this->app()->launchCommand(GenerateServiceProvider::class, [
                false,
                $service_provider_name
            ]);
        }

        $plugin_content = $this->filesystem->read( $service_provider_path );

        $full_name = $this->class_generator->get_fullname($name);
        $id_subscriber = $this->class_generator->create_id( $full_name );

        preg_match('/\$provides = \[(?<content>[^]]*)];/', $plugin_content, $content);
        $content = $content['content'] . " '" . $id_subscriber . "',\n";
        $plugin_content = preg_replace('/\$provides = \[(?<content>[^\]])*];/', "\$provides = [$content           ];", $plugin_content);

        preg_match('/public function register\(\)[^}]*{(?<content>[^}]*)}/', $plugin_content, $content);
        $content = $content['content'] . " \$this->getContainer()->share('" . $id_subscriber . "', $full_name::class);\n";

        $plugin_content = preg_replace('/public function register\(\)[^}]*{(?<content>[^}]*)}/', "public function register()\n{\n$content}", $plugin_content);

        if(! $plugin_content) {
            return;
        }

        $this->filesystem->write($service_provider_path, $plugin_content);

    }
}
