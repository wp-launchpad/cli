<?php

namespace PSR2PluginBuilder\Commands;

use League\Flysystem\Filesystem;
use PSR2PluginBuilder\Entities\Configurations;
use PSR2PluginBuilder\Services\ClassGenerator;

class GenerateTestsCommand extends Command
{
    protected $class_generator;

    protected $configurations;

    protected $filesystem;

    public function __construct(ClassGenerator $class_generator, Configurations $configurations, Filesystem $filesystem)
    {
        parent::__construct('test', 'Generate test classes');

        $this->class_generator = $class_generator;
        $this->configurations = $configurations;
        $this->filesystem = $filesystem;

        $this
            ->argument('<method>', 'The method to test')
            ->option('-t --type', 'Type from the test')
            // Usage examples:
            ->usage(
                '<bold>  test</end> <comment>MyNamespace/ClassName::method --type both</end> ## creates both tests<eol/>' .
                '<bold>  test</end> <comment>MyNamespace/ClassName::method --type unit</end> ## creates unit test<eol/>' .
                '<bold>  test</end> <comment>MyNamespace/ClassName::method --type integration</end> ## creates integration test<eol/>'
            );
    }

    // When app->handle() locates `init` command it automatically calls `execute()`
    // with correct $ball and $apple values
    public function execute($method, $type)
    {
        if(! $type) {
            $type = '';
        }

        $class_name = $method;
        $method_name = false;

        if (strpos($method, "::") !== false) {
            list($class_name, $method_name) = explode("::", $method);
        }

        $methods = [];

        if($method_name) {
            $methods []= $method_name;
        } else {
          $methods = $this->get_public_functions($class_name);
        }

        foreach ($methods as $method) {
            $this->generate_tests($class_name, $method, $type);
        }
    }

    protected function generate_tests(string $class_name, string $method_name, string $type) {
        $namespace = str_replace('\\', '/', $this->configurations->getBaseNamespace());

        $test_namespace_fixture = $namespace . 'Tests/Fixtures/inc/';
        $test_namespace_unit = $namespace . 'Tests/Unit/inc/';
        $test_namespace_integration = $namespace . 'Tests/Integration/inc/';

        $class_name_unit_path = str_replace($namespace, $test_namespace_unit, $class_name);
        $class_name_fixture_path = str_replace($namespace, $test_namespace_fixture, $class_name);
        $class_name_integration_path = str_replace($namespace, $test_namespace_integration, $class_name);

        $method_name_path = $this->class_generator->snake_to_camel_case($method_name);

        $io = $this->app()->io();

        $files = [
            'test/fixture.php.tpl' => $class_name_fixture_path . '/' . $method_name_path,
        ];

        if($type === 'unit' || $type === 'u') {
            $files['test/unit.php.tpl'] = $class_name_unit_path . '/' . $method_name_path;
        }

        if($type === 'integration' || $type === 'i') {
            $files['test/integration.php.tpl'] = $class_name_integration_path . '/' . $method_name_path;
        }

        if(! $type || $type === 'both' || $type === 'b') {
            $files['test/unit.php.tpl'] = $class_name_unit_path . '/' . $method_name_path;
            $files['test/integration.php.tpl'] = $class_name_integration_path . '/' . $method_name_path;
        }

        foreach ($files as $template => $file) {
            $path = $this->class_generator->generate($template, $file, [
                'base_class' => $this->class_generator->get_fullname($class_name),
                'base_method' => $method_name,
            ], true);

            if( ! $path ) {
                $io->write("The class already exists", true);
                return;
            }

            $io->write("The class is created at this path: $path", true);
        }
    }

    protected function get_public_functions(string $class): array {
        if(! $this->class_generator->exists($class)) {
            return [];
        }
        $path = $this->class_generator->generate_path($class);

        $content = $this->filesystem->read($path);

        if(! preg_match_all('/public[ \n]+function[ \n]*(?<name>\w+)[ \n]*\(/', $content, $results) ) {
            return [];
        }

        return array_values(array_filter($results['name'], function ($result) {
            if(preg_match('/^__\w+/', $result)) {
                return false;
            }
            return $result;
        }));
    }
}
