<?php

namespace PSR2PluginBuilder\Services;

use League\Flysystem\Filesystem;
use PSR2PluginBuilder\Entities\Configurations;
use PSR2PluginBuilder\Templating\Renderer;

class ClassGenerator
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
     * @var Configurations
     */
    protected $configurations;

    /**
     * @param Filesystem $filesystem
     * @param Renderer $renderer
     * @param Configurations $configurations
     */
    public function __construct(Filesystem $filesystem, Renderer $renderer, Configurations $configurations)
    {
        $this->filesystem = $filesystem;
        $this->renderer = $renderer;
        $this->configurations = $configurations;
    }

    public function get_basename(string $name ): string {
        return basename( $name );
    }

    public function generate(string $template, string $class_name, array $variables = [], bool $is_test = false) {
        $basename = $this->get_basename( $class_name );
        $namespace = implode('\\', array_slice(explode('/', $class_name), 0, -1));

        $base_namespace = $is_test ? $this->configurations->getBaseNamespace() . 'Tests\\': $this->configurations->getBaseNamespace();

        $base_namespace = str_replace('\\', '/', $base_namespace);

        $code_dir = $is_test ? $this->configurations->getTestDir() : $this->configurations->getCodeDir();

        $path = str_replace(
                [$base_namespace, "/"],
                [$code_dir, DIRECTORY_SEPARATOR],
                $class_name
            ) . '.php';

        if( $this->filesystem->fileExists($path)) {
            return false;
        }

        $variables_renderer = array_merge([
            'namespace' => $namespace,
            'base_namespace' => $this->configurations->getBaseNamespace(),
            'class_name' => $basename
        ],
        $variables);

        $content = $this->renderer->apply_template($template, $variables_renderer);

        $this->filesystem->write($path, $content);

        return $path;
    }

    public function snake_to_camel_case($string) {
        $result = str_replace('_', '', ucwords($string, '_'));
        return lcfirst($result);
    }
}