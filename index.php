<?php

use RocketLauncherBuilder\AppBuilder;

require_once __DIR__ . '/vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

AppBuilder::init(__DIR__);
