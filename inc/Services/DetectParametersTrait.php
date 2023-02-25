<?php

namespace RocketLauncherBuilder\Services;

trait DetectParametersTrait
{
    protected function get_parameters(string $method, string $content) {
        if ( ! preg_match("/public[ \n]+function[ \n]+{$method}[ \n]*\((?<parameters>[^\)])*\)/", $content, $results ) ) {
            return [];
        }
        $parameters = $results['parameters'];

        if(! preg_match_all('/(?<type>\w+)?[ \n]+(?<name>\$\w+)/m', $parameters, $results) ||
            !key_exists('name', $results)) {
            return [];
        }
        $types = $results['type'];
        $names = $results['name'];

        $output = array_combine($names, $types);

        if(! $output) {
            return [];
        }

        return $output;
    }
}