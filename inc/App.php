<?php

use League\CLImate\CLImate;
use League\Flysystem\Filesystem;
use splitbrain\phpcli\CLI;
use splitbrain\phpcli\Options;
use Templating\Renderer;

class App extends CLI
{
    /**
     * @var \ServiceProvider\ServiceProviderInterface[]
     */
    protected $service_providers = [];



    // register options and arguments

    /**
     * @param \ServiceProvider\ServiceProviderInterface[] $service_providers
     */
    public function __construct(array $service_providers)
    {
        parent::__construct();
        $this->service_providers = $service_providers;
    }

    protected function setup(Options $options)
    {
        foreach ($this->service_providers as $service_provider) {
            $service_provider->setup($options);
        }
        $options->setHelp('A very minimal example that does nothing but print a version');
        $options->registerOption('version', 'print version', 'v');
    }

    // implement your code
    protected function main(Options $options)
    {
        foreach ($this->service_providers as $service_provider) {
            $service_provider->execute($options);
        }
        if ($options->getOpt('version')) {
            $this->info('1.0.0');
        } else {
            echo $options->help();
        }
    }

}
