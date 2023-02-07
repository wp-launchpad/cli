<?php

namespace PSR2PluginBuilder;

use Ahc\Cli\Application;

class App extends Application
{
    public function launchCommand(string $command_class, array $params = []) {
        $command_instance = array_reduce($this->commands, function ($result, $command) use ($command_class) {
            if( $result ) {
                return $result;
            }

            if( $command instanceof $command_class) {
                return $command;
            }

            return $result;
        }, null);

        if(! $command_instance) {
            return;
        }

        $command_instance->parse($params);

        $this->doAction($command_instance);
    }
}
