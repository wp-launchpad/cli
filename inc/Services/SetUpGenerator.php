<?php

namespace RocketLauncherBuilder\Services;

use League\Flysystem\Filesystem;
use RocketLauncherBuilder\Templating\Renderer;

class SetUpGenerator
{
    use CreateIDTrait;

    /**
     * Interacts with the filesystem.
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Renderer that handles layout of template files.
     *
     * @var Renderer
     */
    protected $renderer;

    /**
     * Instantiate the class.
     *
     * @param Filesystem $filesystem Interacts with the filesystem.
     * @param Renderer $renderer Renderer that handles layout of template files.
     */
    public function __construct(Filesystem $filesystem, Renderer $renderer)
    {
        $this->filesystem = $filesystem;
        $this->renderer = $renderer;
    }

    /**
     * Generate set_up method.
     *
     * @param string $path Path from the class we generate set_up function for.
     * @param string $fullclass Fullname from the class.
     *
     * @return array
     * @throws \League\Flysystem\FileNotFoundException
     * @throws \RocketLauncherBuilder\Templating\FileNotFoundException
     */
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

    /**
     * Detect if a class is present in the content.
     *
     * @param string $name Name from the class.
     * @param string $content Content to check in.
     *
     * @return bool
     */
    protected function detect_class(string $name, string $content) {
        return (bool) preg_match("/class[\n ]+$name/", $content);
    }

    /**
     * Fetch parameters from the constructor from the class.
     *
     * @param string $content content from the class.
     *
     * @return array<string,string|null>
     */
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

    /**
     * Find the fullname from a class in the content.
     *
     * @param string $name Name from the class.
     * @param string $content Content to search in.
     *
     * @return string
     */
    protected function find_fullname(string $name, string $content) {
        if(! preg_match("/use[ \n]+(?<class>[^;]*$name);/", $content, $results) ) {
            return $name;
        }
        return $results['class'];
    }

    /**
     * Add a setup method to the class.
     *
     * @param string $setup Setup method to add.
     * @param string $content Content from the class.
     *
     * @return string
     */
    public function add_setup_to_class(string $setup, string $content) {
        if(! preg_match('/(?<class>class[ \n]+\w+[ \n]+extends[ \n]+TestCase[ \n]+{)/', $content, $results)) {
            return $content;
        }
        $class = $results['class'] . "\n" . $setup;
        return str_replace($results['class'], $class, $content);
    }

    /**
     * Add import from classes used.
     *
     * @param string[] $usages classes used.
     * @param string $content content from the file.
     *
     * @return string
     */
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
