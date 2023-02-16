<?php

namespace PSR2PluginBuilder\Services;

use League\Flysystem\Filesystem;

class SetUpGenerator
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function generate_set_up(string $path) {
        if(! $this->filesystem->fileExists($path)) {
            return '';
        }

        $content = $this->filesystem->read($path);

        if(! $this->detect_class($content)) {
            return '';
        }


    }

    protected function detect_class(string $content) {

    }
}
