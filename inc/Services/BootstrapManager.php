<?php

namespace RocketLauncherBuilder\Services;

use League\Flysystem\Filesystem;
use RocketLauncherBuilder\Entities\Configurations;
use RocketLauncherBuilder\Templating\Renderer;

class BootstrapManager
{
    const BOOTSTRAP_FILE = '/Integration/bootstrap.php';

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
     * @param Renderer $renderer
     */
    public function __construct(Filesystem $filesystem, Configurations $configuration, Renderer $renderer)
    {
        $this->filesystem = $filesystem;
        $this->configuration = $configuration;
        $this->renderer = $renderer;
    }

    public function add_external_group(string $group) {

        $bootstrap_path = $this->configuration->getTestDir() . self::BOOTSTRAP_FILE;

        if( ! $this->filesystem->has($bootstrap_path) ) {
            return false;
        }

        $content = $this->filesystem->read($bootstrap_path);

        if(! preg_match('/(?<prefix>tests_add_filter\(\s*\'muplugins_loaded\',\s*function\(\) {\s*\n)/', $content, $results)) {
            return false;
        }

        $prefix = $results['prefix'];

        if( preg_match("/BootstrapManager::isGroup\(\s*[\"']{$group}[\"']\s*\)/", $content)) {
            return false;
        }

        $replacement = $prefix . $this->renderer->apply_template('test/_partials/bootstrapintegrationgroup.php.tpl', [
                'group' => $group
            ]);

        $content = str_replace($prefix, $replacement, $content);

        $content = $this->maybe_add_usage($content);

        $this->filesystem->update($bootstrap_path, $content);

        return true;
    }

    protected function maybe_add_usage(string $content) {
        if( preg_match('/use[ \n]+WPMedia\\\PHPUnit\\\BootstrapManager;/', $content)) {
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
