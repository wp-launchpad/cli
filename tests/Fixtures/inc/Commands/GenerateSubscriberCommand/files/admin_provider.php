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
        \PSR2Plugin\Engine\Test\MySubscriber::class,
    ];

    /**
     * Return IDs from admin subscribers.
     *
     * @return string[]
     */
    public function get_admin_subscribers(): array {
        return [
            \PSR2Plugin\Engine\Test\MySubscriber::class,
        ];
    }

    /**
     * Registers items with the container
     *
     * @return void
     */
    public function register()
    {
        $this->getContainer()->share(\PSR2Plugin\Engine\Test\MySubscriber::class, \PSR2Plugin\Engine\Test\MySubscriber::class);
    }
}
