<?php

namespace PSR2PluginBuilder\Services;

use League\Flysystem\Filesystem;
use PSR2PluginBuilder\Templating\Renderer;

class SetUpGenerator
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Renderer
     */
    protected $renderer;

    public function generate_set_up(string $path) {
        if(! $this->filesystem->fileExists($path)) {
            return '';
        }

        $content = $this->filesystem->read($path);

        $name = str_replace('.php', '', basename($path) );

        $main_class = $this->detect_class($name, $content);
        $key_main_class = $this->create_id($main_class);

        if(! $this->detect_class($main_class, $content)) {
            return '';
        }

        $parameters = $this->fetch_parameters($content);

        $properties = '';
        $properties_initialisation = '';
        $init_params = '';

        foreach ($parameters as $key => $type) {
            if($type) {
                $properties .= $this->renderer->apply_template('/test/_partials/parameter.php.tpl', [
                   'type' => $type,
                   'has_type' => true,
                   'name' => $key
                ]);
                $properties_initialisation .= $this->renderer->apply_template('/test/_partials/parameter_init.php.tpl', [
                    'type' => $type,
                    'has_type' => true,
                    'name' => $key
                ]);
                continue;
            }
            $properties .= $this->renderer->apply_template('/test/_partials/parameter.php.tpl', [
                'has_type' => false,
                'name' => $key
            ]);
            $properties_initialisation .= $this->renderer->apply_template('/test/_partials/parameter_init.php.tpl', [
                'has_type' => false,
                'name' => $key
            ]);

            $init_params .= "\$this->$key, ";
        }

        return $this->renderer->apply_template('/test/_partials/setup.php.tpl', [
            'main_class_name' => $key_main_class,
            'main_class_type' => $main_class,
            'properties' => $properties,
            'properties_initialisation' => $properties_initialisation,
            'init_params' => $init_params,
        ]);
    }

    public function create_id(string $class ) {
        $class = trim( $class, '\\' );
        $class = str_replace( '\\', '.', $class );
        return strtolower( preg_replace( ['/([a-z])\d([A-Z])/', '/[^_]([A-Z][a-z])]/'], '$1_$2', $class ) );
    }

    protected function detect_class(string $name, string $content) {
        return preg_match("/class[\n ]+$name/", $content);
    }

    protected function fetch_parameters(string $content): array {
        if(! preg_match('/public[\n ]+function[\n ]+__construct\([\n ]*(?<parameters>[^\)]*)[\n ]*\)/',
            $content,
            $results) || ! key_exists('parameters', $results)) {
            return [];
        }
        $parameters = $results['parameters'];
        if(! preg_match_all('/(?<type>\w+)?[ \n]+(?<name>\$\w+)/m', $content, $results, $parameters) ||
            !key_exists('name', $results)) {
            return [];
        }
        $types = $results['type'];
        $names = $results['name'];

        $ouput = array_combine($names, $types);
        if(! $ouput) {
            return [];
        }
        return $ouput;
    }
}
