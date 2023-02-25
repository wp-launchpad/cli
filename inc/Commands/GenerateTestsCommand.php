<?php

namespace RocketLauncherBuilder\Commands;

use League\Flysystem\Filesystem;
use RocketLauncherBuilder\Entities\Configurations;
use RocketLauncherBuilder\Services\BootstrapManager;
use RocketLauncherBuilder\Services\ClassGenerator;
use RocketLauncherBuilder\Services\ContentGenerator;
use RocketLauncherBuilder\Services\FixtureGenerator;
use RocketLauncherBuilder\Services\ProjectManager;
use RocketLauncherBuilder\Services\SetUpGenerator;

class GenerateTestsCommand extends Command
{
    protected $class_generator;

    protected $configurations;

    protected $filesystem;

    protected $setup_generator;

    protected $fixture_generator;

    protected $bootstrap_manager;

    protected $project_manager;

    protected $content_generator;

    public function __construct(ClassGenerator $class_generator, Configurations $configurations, Filesystem $filesystem, SetUpGenerator $generator, FixtureGenerator $fixture_generator, ContentGenerator $content_generator, BootstrapManager $bootstrap_manager, ProjectManager $project_manager)
    {
        parent::__construct('test', 'Generate test classes');

        $this->class_generator = $class_generator;
        $this->configurations = $configurations;
        $this->filesystem = $filesystem;
        $this->setup_generator = $generator;
        $this->fixture_generator = $fixture_generator;
        $this->bootstrap_manager = $bootstrap_manager;
        $this->project_manager = $project_manager;
        $this->content_generator = $content_generator;

        $this
            ->argument('<method>', 'The method to test')
            ->option('-t --type', 'Type from the test')
            ->option('-g --group', 'Group from the test')
            ->option('-e --expected', 'Force the test to have an expected param from the test')
            ->option('-s --scenarios', 'Add new scenarios for the test')
            ->option('-x --external', 'Add the integration tests as external run')
           // ->option('-n --no-expected', 'Force the test to not have an expected param from the test')
            // Usage examples:
            ->usage(
                '<bold>  test</end> <comment>MyNamespace/ClassName::method --type both</end> ## creates both tests<eol/>' .
                '<bold>  test</end> <comment>MyNamespace/ClassName::method --type unit</end> ## creates unit test<eol/>' .
                '<bold>  test</end> <comment>MyNamespace/ClassName::method --type integration</end> ## creates integration test<eol/>'
            );
    }

    // When app->handle() locates `init` command it automatically calls `execute()`
    // with correct $ball and $apple values
    public function execute($method, $type, $group, $expected, $scenarios, $external)
    {
        if(! $type) {
            $type = '';
        }

        if(! $group) {
            $group = '';
        }

        if(! $expected) {
            $expected = '';
        }

        if(! $external) {
            $external = '';
        }

        if(! $scenarios) {
            $scenarios = [''];
        } else {
            $scenarios = explode(',', $scenarios);
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
            $this->generate_tests($class_name, $method, $type, $group, $expected, $external, $class_name, $scenarios);
        }
    }

    protected function generate_tests(string $class_name, string $method_name, string $type, string $group, string $expected, string $external, string $original_class, array $scenarios) {
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

        $original_class_path = $this->class_generator->generate_path($original_class);


        $has_return = $this->fixture_generator->method_has_return($original_class_path, $method_name);

        if($expected === 'present') {
            $has_return = true;
        }
        if($expected === 'absent') {
            $has_return = false;
        }

        $scenarios = $this->fixture_generator->generate_scenarios($original_class_path, $method_name, $has_return, $scenarios);

        foreach ($files as $template => $file) {
            $has_return = $this->fixture_generator->method_has_return($original_class_path, $method_name);

            $content_test = '';

            if( $template === 'test/unit.php.tpl') {
                $content_test = $this->content_generator->generate_unit($original_class_path, $method_name, $has_return);
            }

            if( $template === 'test/integration.php.tpl') {
                $content_test = $this->content_generator->generate_integration($original_class_path, $method_name, $has_return);
            }

            $path = $this->class_generator->generate($template, $file, [
                'base_class' => $this->class_generator->get_fullname($class_name),
                'base_method' => $method_name,
                'has_group' => $group !== '',
                'has_return' => $has_return,
                'group' => $group,
                'content' => $content_test,
                'scenarios' => $scenarios,
                'external' => $external,
                'has_external' => $external !== '',
            ], true);

            if( ! $path ) {
                $io->write("The class already exists", true);
                continue;
            }

            if( $template === 'test/unit.php.tpl') {
                $setup = $this->setup_generator->generate_set_up($original_class_path, $original_class);
                $content = $this->filesystem->read($path);
                $content = $this->setup_generator->add_usage_to_class($setup['usages'], $content);
                $this->filesystem->update($path, $this->setup_generator->add_setup_to_class($setup['setup'], $content));
            }

            if( $template === 'test/integration.php.tpl' && $external) {
                $this->bootstrap_manager->add_external_group($external);
                $this->project_manager->add_external_test_group($external);
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
