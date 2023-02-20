<?php

namespace RocketLauncherBuilder\Services;

use League\Flysystem\Filesystem;
use RocketLauncherBuilder\Templating\Renderer;

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

    /**
     * @param Filesystem $filesystem
     * @param Renderer $renderer
     */
    public function __construct(Filesystem $filesystem, Renderer $renderer)
    {
        $this->filesystem = $filesystem;
        $this->renderer = $renderer;
    }

    public function generate_set_up(string $path, string $fullclass) {

        $usages = [];

        if(! $this->filesystem->has($path)) {
            return [
                'setup' => '',
                'usages' => $usages
            ];
        }

        $content = $this->filesystem->read($path);

        $name = str_replace('.php', '', basename($path) );

        $has_main_class = $this->detect_class($name, $content);
        $key_main_class = $this->create_id($name);
        if(! $has_main_class) {
            return [
                'setup' => '',
                'usages' => $usages
            ];
        }

        $parameters = $this->fetch_parameters($content);

        $properties = '';
        $properties_initialisation = '';
        $init_params = '';

        $usages[]= trim(str_replace('/', '\\', $fullclass) ,'\\');

        if(count($parameters) > 0) {
            $usages []= 'Mockery';
        }

        foreach ($parameters as $key => $type) {
            $key_without_dollar = str_replace('$', '', $key);
            $init_params .= "\$this->$key_without_dollar, ";
            if($type) {
                $usages []= $this->find_fullname( $type, $content );

                $properties .= $this->renderer->apply_template('/test/_partials/parameter.php.tpl', [
                   'type' => $type,
                   'has_type' => true,
                   'name' => $key
                ]);

                $properties_initialisation .= $this->renderer->apply_template('/test/_partials/parameter_init.php.tpl', [
                    'type' => $type,
                    'has_type' => true,
                    'name' => $key_without_dollar
                ]);
                continue;
            }
            $properties .= $this->renderer->apply_template('/test/_partials/parameter.php.tpl', [
                'has_type' => false,
                'name' => $key
            ]);
            $properties_initialisation .= $this->renderer->apply_template('/test/_partials/parameter_init.php.tpl', [
                'has_type' => false,
                'name' => $key_without_dollar
            ]);

        }

        $setup = $this->renderer->apply_template('/test/_partials/setup.php.tpl', [
            'main_class_name' => $key_main_class,
            'main_class_type' => $name,
            'properties' => $properties,
            'properties_initialisation' => $properties_initialisation,
            'init_params' => trim($init_params, ', '),
        ]);

        $usages = array_unique($usages);
        $usages = array_filter($usages);

        return [
            'setup' => $setup,
            'usages' => $usages
        ];
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
        if(! preg_match_all('/(?<type>\w+)?[ \n]+(?<name>\$\w+)/m', $parameters, $results) ||
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

    protected function find_fullname(string $name, string $content) {
        if(! preg_match("/use[ \n]+(?<class>[^;]*$name);/", $content, $results) ) {
            return $name;
        }
        return $results['class'];
    }

    public function add_setup_to_class(string $setup, string $content) {
        if(! preg_match('/(?<class>class[ \n]+\w+[ \n]+extends[ \n]+TestCase[ \n]+{)/', $content, $results)) {
            return $content;
        }
        $class = $results['class'] . "\n" . $setup;
        return str_replace($results['class'], $class, $content);
    }

    public function add_usage_to_class(array $usages, string $content) {
        $uses = array_reduce($usages, function ($result, $usage) {
            return $result . "use $usage;\n";
        },"\n");

        if(! preg_match('/(?<namespace>namespace[^;]+;)/', $content, $results)) {
            return $content;
        }
        $namespace = $results['namespace'];
        $replace = $namespace . "\n$uses";
        return str_replace($namespace, $replace, $content);
    }
}
