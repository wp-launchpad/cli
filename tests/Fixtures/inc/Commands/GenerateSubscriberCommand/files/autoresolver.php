<?php

namespace PSR2Plugin\Engine\Test;

class ServiceProvider extends Dependencies\RocketLauncherAutoresolver\ServiceProvider
{

    /**
     * Return IDs from common subscribers.
     *
     * @return string[]
     */
    public function get_common_subscribers(): array {
        return [
            \PSR2Plugin\Engine\Test\MySubscriber::class,
        ];
    }

}
