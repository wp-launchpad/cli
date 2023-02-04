<?php

namespace ServiceProvider;

use League\CLImate\CLImate;
use League\Flysystem\Filesystem;
use splitbrain\phpcli\Options;
use Templating\Renderer;

interface ServiceProviderInterface
{
    public function setup(Options $options);

    public function execute(Options $options);

    public function set_filesystem(Filesystem $filesystem);

    public function set_renderer(Renderer $renderer);

    public function set_formatter(CLImate $formatter);
}
