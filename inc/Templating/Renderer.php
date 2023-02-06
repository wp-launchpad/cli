<?php

namespace PSR2PluginBuilder\Templating;

class Renderer
{
    public $templates_folder;

    public function __construct(string $templates_folder)
    {
        $this->templates_folder = $templates_folder;
    }

    /**
     * @throws FileNotFoundException
     */
    public function apply_template(string $template_name, array $variables = []): string
    {
        $template = $this->get_template($template_name);
        foreach ($variables as $variable_name => $variable_value) {
            $template = str_replace("{{ $variable_name }}", $variable_value, $template);
        }

        return $template;
    }

    /**
     * @param string $template
     * @return string
     * @throws FileNotFoundException
     */
    public function get_template(string $template): string
    {
        $template_file = $this->templates_folder . $template;
var_dump($template_file);
        if (!is_file($template_file)) {
            throw new FileNotFoundException("Template file not found.");
        }

        return file_get_contents($template_file);
    }
}
