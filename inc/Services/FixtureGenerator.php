<?php

namespace RocketLauncherBuilder\Services;

use League\Flysystem\Filesystem;

class FixtureGenerator
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function generate_scenarios(string $path, string $method) {
        if(! $this->filesystem->has($path)) {
            return '';
        }

        $content = $this->filesystem->read($path);

        $has_method = $this->has_method($method, $content);

        if( ! $has_method ) {
            return '';
        }

        $parameters = $this->get_parameters($method, $content);


    }

    protected function has_method(string $method, string $content) {

    }

    protected function get_parameters(string $method, string $content) {

    }

    protected function has_return(string $method, string $content) {

    }
}
