<?php

namespace PSR2PluginBuilder\Commands;

use PSR2PluginBuilder\Entities\Configurations;
use PSR2PluginBuilder\Services\ClassGenerator;

class GenerateTestsCommand extends Command
{

    protected $method;

    protected $class_generator;

    protected $configurations;

    public function __construct(ClassGenerator $class_generator, Configurations $configurations)
    {
        parent::__construct('test', 'Generate test classes');

        $this->class_generator = $class_generator;
        $this->configurations = $configurations;

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

        list($class_name, $method_name) = explode("::", $method);

        $namespace = str_replace('\\', '/', $this->configurations->getBaseNamespace());

        $test_namespace_fixture = $namespace . 'Tests/Fixtures/';
        $test_namespace_unit = $namespace . 'Tests/Unit/';
        $test_namespace_integration = $namespace . 'Tests/Integration/';

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
                'base_class' => $class_name,
                'base_method' => $method_name,
            ], true);

            if( ! $path ) {
                $io->write("The class already exists", true);
                return;
            }

            $io->write("The class is created at this path: $path", true);
        }
    }
}