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
     * Is the test mode active.
     *
     * @var bool
     */
    protected static $is_test_mode = false;

    /**
     *  Initiate the CLI.
     *
     * @param string $project_dir Base directory from the project.
     * @param string[] $service_providers Service providers to add to the CLI.
     */
    public static function init(string $project_dir, array $service_providers = [])
    {
        $app = new App('Rocket Launcher', "0.0.3", self::$is_test_mode ? function() {} : null);
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

        $adapter = new Local($project_dir, self::$is_test_mode ? 0 : LOCK_EX);

        // The FilesystemOperator
        $filesystem = new Filesystem($adapter);

        $configs = (new ConfigurationResolver($filesystem, $project_dir))->get_configuration();
        foreach ($service_providers as $service_provider) {
            $instance = new $service_provider($configs, $filesystem, __DIR__ . '/../');
            if(! $instance instanceof ServiceProviderInterface){
                continue;
            }
            $app = $instance->attach_commands($app);
        }
        $app->handle($_SERVER['argv']);
    }

    /**
     * Enable test mode.
     *
     * @return void
     */
    public static function enable_test_mode() {
        self::$is_test_mode = true;
    }
}
