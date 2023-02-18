<?php

namespace PSR2PluginBuilder\Services;

use League\Flysystem\Filesystem;
use PSR2PluginBuilder\Templating\Renderer;

class SetUpGenerator
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Renderer
     */
    protected $renderer;

    public function generate_set_up(string $path) {
        if(! $this->filesystem->fileExists($path)) {
            return '';
        }

        $content = $this->filesystem->read($path);

        if(! $this->detect_class($content)) {
            return '';
        }

        $parameters = $this->fetch_parameters($content);

        $properties = '';



        foreach ($parameters as $key => $type) {
            $docblock = '';
            if($type) {
                $docblock = $this->renderer->apply_template('', [
                    'type' => $type
                ]);
                $properties .= $this->renderer->apply_template('', [
                   'docblock' => $docblock,
                   'name' => $key
                ]);
            }
        }
    }

    protected function detect_class(string $name, string $content) {
        return preg_match("/class[\n ]+$name/", $content);
    }

    protected function fetch_parameters(string $content): array {
        if(! preg_match('/public[\n ]+function[\n ]+__construct\([\n ]*(?<parameters>[^\)]*)[\n ]*\)/',
            $content,
            $results) || ! key_exists('parameters', $results)) {
            return [];
        }
        $parameters = $results['parameters'];
        if(! preg_match('/(?<type>\w+)?[ \n]+(?<name>\$\w+)/', $content, $results, $parameters) || !key_exists('name', $results)) {
            return [];
        }
        $types = $results['type'];
        $names = $results['name'];

        $ouput = array_combine($names, $types);
        if(! $ouput) {
            return [];
        }
        return $ouput;
    }
}
