<?php

use PSR2PluginBuilder\AppBuilder;
use PSR2PluginBuilder\ServiceProviders\BaseServiceProvider;

require_once __DIR__ . '/vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

AppBuilder::init(__DIR__, [
    BaseServiceProvider::class,
]);
