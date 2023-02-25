<?php

namespace RocketLauncherBuilder\Services;

trait DetectReturnTrait
{
    protected function has_return(string $method, string $content) {

        if(preg_match("/\/\*\*(?<docblock>[^\/]+)\/[ \n]+public[ \n]+function[ \n]+{$method}/", $content, $results) && preg_match('/@return (void|null)/', $results['docblock'])) {
            return false;
        }

        if(! preg_match("/public[ \n]+function[ \n]+{$method}[ \n]*\(([^\)])*\)(?<return>[ \n]*:[ \n]*\w+)?[ \n]*(?<content>\{((?:[^{}]+|\{(?3)\})*)\})/", $content, $results ) ) {
            return false;
        }

        $content = $results['content'];

        if(key_exists('return', $results) && strpos($results['return'], 'void') === false) {
            return true;
        }

        return preg_match('/return\s+([^;]+);/', $content);
    }
}
