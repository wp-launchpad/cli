<?php

namespace PSR2PluginBuilder;

use Ahc\Cli\Application;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PSR2PluginBuilder\ServiceProviders\ServiceProviderInterface;
use PSR2PluginBuilder\Services\ConfigurationResolver;

class AppBuilder
{

    /**
     * @param string[] $service_providers
     */
    public static function init(string $project_dir, array $service_providers)
    {
        $app = new Application('PS2 plugin builder');
        $app->logo('Ascii art logo of your app');

        $adapter = new LocalFilesystemAdapter(
        // Determine root directory
            $project_dir
        );

        // The FilesystemOperator
        $filesystem = new Filesystem($adapter);

        $configs = (new ConfigurationResolver($filesystem, $project_dir))->get_configuration();
        foreach ($service_providers as $service_provider) {
            $instance = new $service_provider($configs, $project_dir);
            if(! $instance instanceof ServiceProviderInterface){
                continue;
            }
            $app = $instance->attach_commands($app);
        }

        $app->handle($_SERVER['argv']);
    }

}
