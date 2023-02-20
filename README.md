# rocket-launcher-builder
Rocket Launcher Builder is the library containing the CLI tool used to generate new files in the Rocket Launcher project.

## Installation

To install the library first launch the following command: `composer require CrochetFeve0251/rocket-launcher-builder --dev`

Then at the root from your project you can create a `builder` file with the following content:
```php

#!/usr/bin/php
<?php
use RocketLauncherBuilder\AppBuilder;

require_once __DIR__ . '/../vendor/autoload.php';

AppBuilder::init(__DIR__ . '/../');
```

## Usage

With this commandline the following command are available:

    `subscriber`: Generate a subscriber file and attach it to the project.
    `provider`: Generate a service provider file and attach it to the project.
    `test`: Generate a test file.
    `table`: Generate files for adding a new table to the project.
