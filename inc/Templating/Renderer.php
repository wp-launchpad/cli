<?php

namespace PSR2PluginBuilder\Templating;

use League\Flysystem\Filesystem;

class Renderer
{
    public $templates_folder;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function __construct(Filesystem $filesystem, string $templates_folder)
    {
        $this->filesystem = $filesystem;
        $this->templates_folder = $templates_folder;
    }

    /**
     * @throws FileNotFoundException
     */
    public function apply_template(string $template_name, array $variables = []): string
    {
        $template = $this->get_template($template_name);

        $template = $this->apply_ifs($template, $variables);

        foreach ($variables as $variable_name => $variable_value) {
            $template = str_replace("{{ $variable_name }}", $variable_value, $template);
        }

        return $template;
    }

    protected function apply_ifs(string $content, array $variables = []): string {
        if ( preg_match_all('/\{%[ \n]+if[ \n]+(?<condition>[^:]+):[ \n]+%\}(?<content_if>[\s\S]*?)(\{%[ \n]+else[ \n]+%\}(?<content_else>)[\s\S]*?)?\{%[ \n]+endif[ \n]+%\}/', $content, $results)) {
            return $content;
        }
        return $content;
        $results['condition'];
        $results['content_if'];
        $results['content_else'];
    }

    /**
     * @param string $template
     * @return string
     * @throws FileNotFoundException
     */
    public function get_template(string $template): string
    {
        $template_file = $this->templates_folder . $template;
        if (!$this->filesystem->has($template_file)) {
            throw new FileNotFoundException("Template file not found.");
        }

        return $this->filesystem->read($template_file);
    }
}
