<?php

namespace PSR2PluginBuilder\ServiceProviders;

use Ahc\Cli\Application;

interface ServiceProviderInterface
{
    public function attach_commands(Application $app): Application;
}
