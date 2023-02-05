<?php

namespace PSR2PluginBuilder\ServiceProviders;

class ClassGenerator
{
    public function generate(string $template, array $variables) {
        $basename = basename( $name );
        $namespace = implode('\\', array_slice(explode('/', $name), 0, -1));
        $io = $this->app()->io();

        $base_namespace = str_replace('\\', '/', $this->configurations->getBaseNamespace());

        $path = str_replace(
                [$base_namespace, "/"],
                [$this->configurations->getCodeDir(), DIRECTORY_SEPARATOR],
                $name
            ) . '.php';

        if( $this->filesystem->fileExists($path)) {
            $io->write("The class already exists", true);
            return;
        }

        $content = $this->renderer->apply_template('subscriber.php.tpl', [
            'namespace' => $namespace,
            'base_namespace' => $this->configurations->getBaseNamespace(),
            'class_name' => $basename
        ]);

        $this->filesystem->write($path, $content);

        $io->write("The subscriber is created at this path: $path", true);
    }
}