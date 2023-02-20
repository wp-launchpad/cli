<?php

namespace RocketLauncherBuilder\ServiceProviders;

use RocketLauncherBuilder\App;

interface ServiceProviderInterface
{
    public function attach_commands(App $app): App;
}
