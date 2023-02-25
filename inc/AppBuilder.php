<?php

namespace RocketLauncherBuilder;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use RocketLauncherBuilder\ServiceProviders\BaseServiceProvider;
use RocketLauncherBuilder\ServiceProviders\ServiceProviderInterface;
use RocketLauncherBuilder\Services\ConfigurationResolver;

class AppBuilder
{

    /**
     * @param string[] $service_providers
     */
    public static function init(string $project_dir, array $service_providers = [])
    {
        $app = new App('Rocket Launcher', "0.0.3");
        $app->logo("
  ____            _        _     _                           _               
 |  _ \ ___   ___| | _____| |_  | |    __ _ _   _ _ __   ___| |__   ___ _ __ 
 | |_) / _ \ / __| |/ / _ \ __| | |   / _` | | | | '_ \ / __| '_ \ / _ \ '__|
 |  _ < (_) | (__|   <  __/ |_  | |__| (_| | |_| | | | | (__| | | |  __/ |   
 |_| \_\___/ \___|_|\_\___|\__| |_____\__,_|\__,_|_| |_|\___|_| |_|\___|_|   
                                                                             ");

        $service_providers = array_merge($service_providers, [
            BaseServiceProvider::class,
        ]);

        $adapter = new Local($project_dir);

        // The FilesystemOperator
        $filesystem = new Filesystem($adapter);

        $configs = (new ConfigurationResolver($filesystem, $project_dir))->get_configuration();
        foreach ($service_providers as $service_provider) {
            $instance = new $service_provider($configs, $project_dir, __DIR__ . '/../');
            if(! $instance instanceof ServiceProviderInterface){
                continue;
            }
            $app = $instance->attach_commands($app);
        }

        $app->handle($_SERVER['argv']);
    }

}
