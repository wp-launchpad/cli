<?php

namespace PSR2PluginBuilder\Commands;

use League\Flysystem\Filesystem;
use PSR2PluginBuilder\ObjectValues\SubscriberType;
use PSR2PluginBuilder\Services\ClassGenerator;
use PSR2PluginBuilder\Services\ProviderManager;
use PSR2PluginBuilder\Templating\Renderer;

class GenerateSubscriberCommand extends Command
{
    protected $name;

    protected $type;
    /**
     * @var ClassGenerator
     */
    protected $class_generator;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var ProviderManager
     */
    protected $service_provider_manager;

    public function __construct(ClassGenerator $class_generator, Filesystem $filesystem, ProviderManager $service_provider_manager)
    {
        parent::__construct('subscriber', 'Generate subscriber class');

        $this->class_generator = $class_generator;
        $this->filesystem = $filesystem;
        $this->service_provider_manager = $service_provider_manager;

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
    public function execute($name, $type)
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
        $id_subscriber = $this->class_generator->create_id( $name );

        $this->service_provider_manager->add_class($service_provider_path, $name);

        if( ! $type || $type === 'c' || $type === 'common') {
            $this->service_provider_manager->register_subscriber($id_subscriber, $service_provider_path, new SubscriberType(SubscriberType::COMMON));
        }

        if($type === 'a' || $type === 'admin') {
            $this->service_provider_manager->register_subscriber($id_subscriber, $service_provider_path, new SubscriberType(SubscriberType::ADMIN));
        }

        if($type === 'f' || $type === 'front') {
            $this->service_provider_manager->register_subscriber($id_subscriber, $service_provider_path, new SubscriberType(SubscriberType::FRONT));
        }

    }
}
