<?php

namespace RocketLauncherBuilder\ServiceProviders;

use RocketLauncherBuilder\App;

interface ServiceProviderInterface
{
    /**
     * Attach commands from the service provider to the cli.
     *
     * @param App $app cli.
     *
     * @return App
     */
    public function attach_commands(App $app): App;
}
