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
        'psr2plugin.engine.test.database.queries.mytable',
        'psr2plugin.engine.test.database.tables.mytable',
        'psr2plugin.engine.test.database.rows.mytable',
        'psr2plugin.engine.test.database.schemas.mytable',
    ];

    /**
     * Registers items with the container
     *
     * @return void
     */
    public function register()
    {
        $this->getContainer()->share('psr2plugin.engine.test.database.queries.mytable', \PSR2Plugin\Engine\Test\Database\Queries\MyTable::class);
        $this->getContainer()->get('psr2plugin.engine.test.database.queries.mytable');
        $this->getContainer()->share('psr2plugin.engine.test.database.tables.mytable', \PSR2Plugin\Engine\Test\Database\Tables\MyTable::class);
        $this->getContainer()->get('psr2plugin.engine.test.database.tables.mytable');
        $this->getContainer()->share('psr2plugin.engine.test.database.rows.mytable', \PSR2Plugin\Engine\Test\Database\Rows\MyTable::class);
        $this->getContainer()->get('psr2plugin.engine.test.database.rows.mytable');
        $this->getContainer()->share('psr2plugin.engine.test.database.schemas.mytable', \PSR2Plugin\Engine\Test\Database\Schemas\MyTable::class);
        $this->getContainer()->get('psr2plugin.engine.test.database.schemas.mytable');
    }
}
