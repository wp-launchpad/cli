<?php

namespace PSR2Plugin\Engine\Test\Database;

class ServiceProvider extends Dependencies\RocketLauncherAutoresolver\ServiceProvider
{

    /**
     * Get class that needs to be instantiated.
     *
     * @return string[]
     */
    public function get_class_to_instantiate(): array {
        return [
            \PSR2Plugin\Engine\Test\Database\Tables\MyTable::class,
        ];
    }

}
