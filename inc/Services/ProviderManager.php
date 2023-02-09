<?php

namespace PSR2PluginBuilder\Services;

use League\Flysystem\Filesystem;
use PSR2PluginBuilder\App;
use PSR2PluginBuilder\Commands\GenerateServiceProvider;
use PSR2PluginBuilder\ObjectValues\SubscriberType;
use PSR2PluginBuilder\Templating\Renderer;

class ProviderManager
{
    /**
     * @var App
     */
    protected $app;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var ClassGenerator
     */
    protected $class_generator;

    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @param App $app
     * @param Filesystem $filesystem
     * @param ClassGenerator $class_generator
     */
    public function __construct(App $app, Filesystem $filesystem, ClassGenerator $class_generator, Renderer $renderer)
    {
        $this->app = $app;
        $this->filesystem = $filesystem;
        $this->class_generator = $class_generator;
        $this->renderer = $renderer;
    }

    public function maybe_generate_service_provider(string $namespace ) {
        $service_provider_path = $this->class_generator->get_dirname( $namespace ) . '/ServiceProvider.php';
        $service_provider_name = $this->class_generator->get_dirname( $namespace ) . '/ServiceProvider';

        if( ! $this->class_generator->exists( $service_provider_path ) ) {
            $this->app->launchCommand(GenerateServiceProvider::class, [
                false,
                $service_provider_name
            ]);
        }
    }

    public function add_class(string $path, string $class) {
        $provider_path = $this->class_generator->generate_path( $path. '/ServiceProvider' );
        $provider_content = $this->filesystem->read( $provider_path );

        $full_name = $this->class_generator->get_fullname( $class );
        $id = $this->class_generator->create_id( $full_name );
        preg_match('/\$provides = \[(?<content>[^]]*)];/', $provider_content, $content);
        $content = $content['content'] . " '" . $id . "',\n";
        $provider_content = preg_replace('/\$provides = \[(?<content>[^\]])*];/', "\$provides = [$content           ];", $provider_content);

        preg_match( '/public function register\(\)[^}]*{(?<content>[^}]*)}/', $provider_content, $content );
        $content = $content['content'] . " \$this->getContainer()->share('" . $id . "', $full_name::class);\n";

        $provider_content = preg_replace( '/public function register\(\)[^}]*{(?<content>[^}]*)}/', "public function register()\n{\n$content}", $provider_content );

        $this->filesystem->write( $provider_path, $provider_content );
    }

    public function register_subscriber(string $id, string $path, SubscriberType $type) {
        $provider_path = $this->class_generator->generate_path($path. '/ServiceProvider.php');
        $provider_content = $this->filesystem->read( $provider_path );

        if ( $type === SubscriberType::FRONT ) {
            $provider_content = $this->add_to_subscriber_method($id, 'get_front_subscribers', $provider_content);
        }
        if ( $type === SubscriberType::ADMIN ) {
            $provider_content = $this->add_to_subscriber_method($id, 'get_admin_subscribers', $provider_content);
        }
        if ( $type === SubscriberType::COMMON ) {
            $provider_content = $this->add_to_subscriber_method($id, 'get_common_subscribers', $provider_content);
        }

        $this->filesystem->write($provider_path, $provider_content);
    }

    protected function add_to_subscriber_method($id, string $method, string $content): string {
        if ( ! preg_match('/public function ' . $method . '\(\)[^}]*{(?<content>[^}]*)}/', $content, $results ) ) {
            preg_match('/(?<content>\$provides = \[[^]]*];)/', $content, $results);
            $new_content = $this->renderer->apply_template('serviceprovider/_partials/' . $method . '.php.tpl', [
                'ids' => "'$id',"
            ]);
            $results = $results['content'] . $new_content;
            return preg_replace('/(?<content>\$provides = \[[^]]*];)/', $results, $content);
        }
        $results = $results['content'] . " '" . $id . "',\n";

        return preg_replace('/public function ' . $method . '\(\)[^}]*{(?<content>[^}]*)}/', "public function $method()\n{\n$results}", $content);
    }

    public function instantiate(string $class, string $path) {
        $provider_path = $this->class_generator->generate_path( $path. '/ServiceProvider.php' );
        $provider_content = $this->filesystem->read( $provider_path );

        $full_name = $this->class_generator->get_fullname( $class );
        $id = $this->class_generator->create_id( $full_name );

        preg_match( '/public function register\(\)[^}]*{(?<content>[^}]*)}/', $provider_content, $content );
        $content = $content['content'] . " \$this->getContainer()->get('" . $id . ");\n";

        $provider_content = preg_replace( '/public function register\(\)[^}]*{(?<content>[^}]*)}/', "public function register()\n{\n$content}", $provider_content );

        $this->filesystem->write( $provider_path, $provider_content );
    }
}