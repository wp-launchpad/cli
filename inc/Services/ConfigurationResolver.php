<?php

namespace PSR2PluginBuilder\Services;

use League\Flysystem\Filesystem;
use PSR2PluginBuilder\Entities\Configurations;

class ConfigurationResolver
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $project_dir;

    /**
     * @param Filesystem $filesystem
     * @param string $project_dir
     */
    public function __construct(Filesystem $filesystem, string $project_dir)
    {
        $this->filesystem = $filesystem;
        $this->project_dir = $project_dir;
    }

    public function get_configuration() {
        $composer_json = json_decode($this->filesystem->read( 'composer.json'), true);
        $namespaces = array_keys($composer_json["autoload"]["psr-4"]);

        $test_namespaces = array_keys($composer_json["autoload-dev"]["psr-4"]);

        $namespace = array_shift( $namespaces );
        $test_namespace = array_shift( $test_namespaces );
        $base_dir = $composer_json["autoload"]["psr-4"][$namespace];
        $test_dir = $composer_json["autoload-dev"]["psr-4"][$test_namespace];

        return new Configurations($namespace, $base_dir, $test_dir?: '');
    }
}