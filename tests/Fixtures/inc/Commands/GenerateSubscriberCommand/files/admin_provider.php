<?php

namespace PSR2Plugin\Engine\Test;

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
        'psr2plugin.engine.test.mysubscriber',
    ];

    /**
     * Return IDs from admin subscribers.
     *
     * @return string[]
     */
    public function get_admin_subscribers(): array {
        return [
            'psr2plugin.engine.test.mysubscriber',
        ];
    }

    /**
     * Registers items with the container
     *
     * @return void
     */
    public function register()
    {
        $this->getContainer()->share('psr2plugin.engine.test.mysubscriber', \PSR2Plugin\Engine\Test\MySubscriber::class);
    }
}
