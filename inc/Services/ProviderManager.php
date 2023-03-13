<?php

namespace RocketLauncherBuilder\Services;

use League\Flysystem\Filesystem;
use RocketLauncherBuilder\App;
use RocketLauncherBuilder\Commands\GenerateServiceProvider;
use RocketLauncherBuilder\ObjectValues\SubscriberType;
use RocketLauncherBuilder\Templating\Renderer;

class ProviderManager
{
    /**
     * @var App
     */
    protected $app;

    /**
     * Interacts with the filesystem.
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Class generator.
     *
     * @var ClassGenerator
     */
    protected $class_generator;

    /**
     * Renderer that handles layout of template files.
     *
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var ProjectManager
     */
    protected $project_manager;

    /**
     * Instantiate the class.
     *
     * @param App $app CLI.
     * @param Filesystem $filesystem Interacts with the filesystem.
     * @param ClassGenerator $class_generator Class generator.
     * @param Renderer $renderer Renderer that handles layout of template files.
     */
    public function __construct(App $app, Filesystem $filesystem, ClassGenerator $class_generator, Renderer $renderer, ProjectManager $project_manager)
    {
        $this->app = $app;
        $this->filesystem = $filesystem;
        $this->class_generator = $class_generator;
        $this->renderer = $renderer;
        $this->project_manager = $project_manager;
    }

    /**
     * Generate a service provider if it doesn't exist.
     *
     * @param string $namespace Namespace to create the service provider in.
     *
     * @return void
     */
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

    /**
     * Register a new class to the service provider.
     *
     * @param string $path Path to the service provider.
     * @param string $class Class to add to the service provider.
     *
     * @return void
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function add_class(string $path, string $class) {
        if( $this->project_manager->is_resolver_present() ) {
            return;
        }
        $provider_path = $this->class_generator->generate_path( $path. '/ServiceProvider' );
        $provider_content = $this->filesystem->read( $provider_path );

        $full_name = $this->class_generator->get_fullname( $class );
        $id = $this->class_generator->get_fullname( $full_name ) . '::class';
        if(! preg_match('/\n(?<indents>\s*)(protected\s)?\$provides = \[(?<content>[^]]*[^ ]*) *];/', $provider_content, $content) ) {
            return;
        }
        if(! trim($content['content'], " \n")) {
            $content['content'] = "\n";
        } else {
            $content['content'] = rtrim($content['content'], " \n") . "\n";
        }

        $indents = $content['indents'];
        $content = $content['content'] . "$indents    " . $id . ",\n";
        $provider_content = preg_replace('/\$provides = \[(?<content>[^\]])*];/', "\$provides = [$content$indents];", $provider_content);

        preg_match( '/\n(?<indents> *)public function register\(\)\s*{ *\n(?<content>[^}]*)}/',
            $provider_content,
            $content );

        $indents = $content['indents'];
        if(! trim($content['content'], " \n")) {
            $content['content'] = '';
        } else {
            $content['content'] = rtrim($content['content'], " \n") . "\n";
        }

        $content = $content['content'] . "$indents    \$this->getContainer()->share(" . $id . ", $full_name::class);\n";
        $provider_content = preg_replace( '/public function register\(\)[^}]*{ *\n(?<content>[^}]*)}/', "public function register()\n$indents{\n$content$indents}", $provider_content );

        $this->filesystem->update( $provider_path, $provider_content );
    }

    /**
     * Register a subscriber to the service provider.
     *
     * @param string $id ID from the subscriber.
     * @param string $path Path from the service provider.
     * @param SubscriberType $type Type from the subscriber.
     *
     * @return void
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function register_subscriber(string $id, string $path, SubscriberType $type) {
        $provider_path = $this->class_generator->generate_path($path. '/ServiceProvider');
        $provider_content = $this->filesystem->read( $provider_path );

        if ( $type->getValue() === SubscriberType::FRONT ) {
            $provider_content = $this->add_to_subscriber_method($id, 'get_front_subscribers', $provider_content);
        }
        if ( $type->getValue() === SubscriberType::ADMIN ) {
            $provider_content = $this->add_to_subscriber_method($id, 'get_admin_subscribers', $provider_content);
        }
        if ( $type->getValue() === SubscriberType::COMMON ) {
            $provider_content = $this->add_to_subscriber_method($id, 'get_common_subscribers', $provider_content);
        }

        $this->filesystem->update($provider_path, $provider_content);
    }

    /**
     * Add the subscriber to the method.
     *
     * @param string $id ID from the subscriber.
     * @param string $method Method to register the subscriber to.
     * @param string $content Content from the service provider.
     *
     * @return string
     * @throws \RocketLauncherBuilder\Templating\FileNotFoundException
     */
    protected function add_to_subscriber_method(string $id, string $method, string $content): string {
        if ( ! preg_match('/\n(?<indents> *)public function ' . $method . '\(\)[^{]*{(?<content>[^}]*)}/', $content, $results ) ) {
           if($this->is_autoresolver($content)) {
               return $this->add_method_autoresolve($content, $method, $id);
           }
           return $this->add_method_vanilla($content, $method, $id);
        }
        $indents = $results['indents'];
        $results = $results['content'] . " " . $id . ",\n";
        return preg_replace('/public function ' . $method . '\(\)[^}]*{(?<content>[^}]*)}/', "public function $method()\n$indents{\n$results$indents}", $content);
    }

    protected function add_method_autoresolve(string $content, string $method, string $id) {
        if(! preg_match('/class(?<content>\s*[^{]*{\h*\n)/', $content, $results)) {
            return $content;
        }
        $new_content = $this->renderer->apply_template('serviceprovider/_partials/' . $method . '.php.tpl', [
            'ids' => "$id,"
        ]);
        $results = $results['content'] . $new_content;
        return preg_replace('/class(?<content>\s*[^{]*{\h*\n)/', $results, $content);
    }

    protected function add_method_vanilla(string $content, string $method, string $id){
        preg_match('/(?<content>\$provides = \[[^]]*];)/', $content, $results);
        $new_content = $this->renderer->apply_template('serviceprovider/_partials/' . $method . '.php.tpl', [
            'ids' => "$id,"
        ]);
        $results = $results['content'] . $new_content;
        return preg_replace('/(?<content>\$provides = \[[^]]*];) *\n/', $results, $content);
    }

    /**
     * Instantiate a class inside the service provider.
     *
     * @param string $path Path from the service provider.
     * @param string $class Class to add to the service provider.
     *
     * @return void
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function instantiate(string $path, string $class) {
        $provider_path = $this->class_generator->generate_path( $path. '/ServiceProvider' );
        $provider_content = $this->filesystem->read( $provider_path );

        $full_name = $this->class_generator->get_fullname( $class );
        $id = $full_name . '::class';

        preg_match( '/\n(?<indents> *)public function register\(\)[^}]*\s*{(?<content>[^}]*)}/', $provider_content, $content );

        $indents = $content['indents'];
        $content = $content['content'] . "$indents\$this->getContainer()->get(" . $id . ");\n";

        $provider_content = preg_replace( '/public function register\(\)[^}]*{(?<content>[^}]*)}/', "public function register()\n$indents{"."$content$indents}", $provider_content );

        $this->filesystem->update( $provider_path, $provider_content );
    }


    public function is_autoresolver(string $content) {
        return (bool) preg_match('/Dependencies\\\\RocketLauncherAutoresolver\\\\ServiceProvider/', $content);
    }
}
