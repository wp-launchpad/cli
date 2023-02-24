<?php

namespace RocketLauncherBuilder\Services;

class ContentGenerator
{
    use DetectReturnTrait;

    public function generate(string $path, string $method, bool $has_return) {

    }

    public function detect_filter(string $method, string $content) {

    }

    protected function get_events(string $method, string $content) {
        if(! preg_match('/public[ \n]+function[ \n]+get_subscribed_events[ \n]*\(([^\)])*\)([ \n]*:[ \n]*\w+)?[ \n]*(?<content>\{((?:[^{}]+|\{(?3)\})*)\})/', $content, $results)) {
            return [];
        }

        $content = $results['content'];

        if(! preg_match_all('/return\s+(?<value>[^;]+);/', $content, $results)) {
            return [];
        }

        $values = $results['values'];

        $events = [];

        foreach ($values as $value) {

            if( ! preg_match_all("/[\"'](?<event>[^'\"]*)[\"'][ \n]*=>[^=]*[\"']{$method}[\"']/", $value, $results)) {
                continue;
            }

            $events = array_merge($events, $results['event']);
        }

        return array_unique($events);
    }
}
