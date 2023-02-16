<?php

namespace PSR2PluginBuilder\ServiceProviders;

use PSR2PluginBuilder\App;

interface ServiceProviderInterface
{
    public function attach_commands(App $app): App;
}
