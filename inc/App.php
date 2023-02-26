<?php

namespace RocketLauncherBuilder;

use Ahc\Cli\Application;

class App extends Application
{
    /**
     * Launch a command from PHP code.
     *
     * @param string $command_class class from the command to launch.
     * @param array $params params from the command.
     *
     * @return void
     */
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
