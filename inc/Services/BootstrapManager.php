<?php

namespace RocketLauncherBuilder\Services;

use League\Flysystem\Filesystem;
use RocketLauncherBuilder\Entities\Configurations;
use RocketLauncherBuilder\Templating\Renderer;

class BootstrapManager
{
    const BOOTSTRAP_FILE = '';

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Configurations
     */
    protected $configuration;

    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @param Filesystem $filesystem
     * @param Configurations $configuration
     */
    public function __construct(Filesystem $filesystem, Configurations $configuration)
    {
        $this->filesystem = $filesystem;
        $this->configuration = $configuration;
    }

    public function add_external_group(string $group) {

        $bootstrap_path = $this->configuration->getTestDir() . self::BOOTSTRAP_FILE;

        if( ! $this->filesystem->has($bootstrap_path) ) {
            return false;
        }

        $content = $this->filesystem->read($bootstrap_path);

        if(! preg_match('/(?<prefix>tests_add_filter\(.*\'muplugins_loaded\',[ \n]*function\(\) {)/', $content, $results)) {
            return false;
        }

        $prefix = $results['prefix'];

        $replacement = $prefix . $this->renderer->apply_template('test/_partials/bootstrapintegrationgroup.php.tpl', [
                'group' => $group
            ]);

        $content = str_replace($prefix, $replacement, $content);

        $content = $this->maybe_add_usage($content);

        $this->filesystem->update($bootstrap_path, $content);

        return true;
    }

    protected function maybe_add_usage(string $content) {
        if( preg_match('/use[ \n]+WPMedia\\PHPUnit\\BootstrapManager;/', $content)) {
            return $content;
        }

        if(! preg_match('/(?<namespace>namespace[^;]+;)/', $content, $results)) {
            return $content;
        }

        $namespace = $results['namespace'];

        $replacement = $namespace . "\nuse WPMedia\PHPUnit\BootstrapManager;";

        return str_replace($namespace, $replacement, $content);
    }
}
