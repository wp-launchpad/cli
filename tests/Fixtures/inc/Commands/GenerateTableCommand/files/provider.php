<?php

namespace PSR2Plugin\Engine\Test\Database;

use PSR2Plugin\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider.
 */
class ServiceProvider extends AbstractServiceProvider
{

    /**
     * The provided array is a way to let the container
     * know that a service is provided by this service
     * provider. Every service that is registered via
     * this service provider must have an alias added
     * to this array or it will be ignored.
     *
     * @var array
     */
    protected $provides = [
        \PSR2Plugin\Engine\Test\Database\Queries\MyTable::class,
        \PSR2Plugin\Engine\Test\Database\Tables\MyTable::class,
        \PSR2Plugin\Engine\Test\Database\Rows\MyTable::class,
        \PSR2Plugin\Engine\Test\Database\Schemas\MyTable::class,
    ];

    /**
     * Registers items with the container
     *
     * @return void
     */
    public function register()
    {
        $this->getContainer()->share(\PSR2Plugin\Engine\Test\Database\Queries\MyTable::class, \PSR2Plugin\Engine\Test\Database\Queries\MyTable::class);
        $this->getContainer()->get(\PSR2Plugin\Engine\Test\Database\Queries\MyTable::class);
        $this->getContainer()->share(\PSR2Plugin\Engine\Test\Database\Tables\MyTable::class, \PSR2Plugin\Engine\Test\Database\Tables\MyTable::class);
        $this->getContainer()->get(\PSR2Plugin\Engine\Test\Database\Tables\MyTable::class);
        $this->getContainer()->share(\PSR2Plugin\Engine\Test\Database\Rows\MyTable::class, \PSR2Plugin\Engine\Test\Database\Rows\MyTable::class);
        $this->getContainer()->get(\PSR2Plugin\Engine\Test\Database\Rows\MyTable::class);
        $this->getContainer()->share(\PSR2Plugin\Engine\Test\Database\Schemas\MyTable::class, \PSR2Plugin\Engine\Test\Database\Schemas\MyTable::class);
        $this->getContainer()->get(\PSR2Plugin\Engine\Test\Database\Schemas\MyTable::class);
    }
}
