<?php

namespace RocketLauncherBuilder\Services;

use League\Flysystem\Filesystem;
use RocketLauncherBuilder\Templating\Renderer;

class ContentGenerator
{
    use DetectReturnTrait, DetectParametersTrait;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @param Filesystem $filesystem
     * @param Renderer $renderer
     */
    public function __construct(Filesystem $filesystem, Renderer $renderer)
    {
        $this->filesystem = $filesystem;
        $this->renderer = $renderer;
    }

    public function generate_integration(string $path, string $method, bool $has_return) {
        return $this->generate($path, $method, $has_return, '/test/_partials/contentintegration.php.tpl');
    }

    protected function generate(string $path, string $method, bool $has_return, string $template, bool $has_event = true) {
        if(! $this->filesystem->has($path)) {
            return '';
        }

        $class_name = str_replace('.php', '', basename($path));
        $class_name = $this->create_id($class_name);

        $content = $this->filesystem->read($path);

        $parameters = $this->get_parameters($method, $content);
        $events = $this->get_events($method, $content);
        $is_action = $this->has_return($method, $content);

        if( count($events) === 0 && $has_event) {
            return '';
        }

        $event = array_pop($events);

        $init = '';

        foreach ($parameters as $key => $type) {
            $key_without_dollar = str_replace('$', '', $key);

            $init .= "\$config['$key_without_dollar'], ";
        }

        return $this->renderer->apply_template(
            $template,
            [
                'class' => $class_name,
                'parameters' => trim($init, ', '),
                'event'      => $event,
                'method' => $method,
                'has_return' => $has_return,
                'is_action' => $is_action,
            ]
        );
    }

    public function generate_unit(string $path, string $method, bool $has_return) {
        return $this->generate($path, $method, $has_return, '/test/_partials/contentunit.php.tpl', false);
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

    protected function create_id(string $class ) {
        $class = trim( $class, '\\' );
        $class = str_replace( '\\', '.', $class );
        return strtolower( preg_replace( ['/([a-z])\d([A-Z])/', '/[^_]([A-Z][a-z])]/'], '$1_$2', $class ) );
    }

}
