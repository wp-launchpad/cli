<?php

namespace PSR2PluginBuilder\Commands;

use PSR2PluginBuilder\Entities\Configurations;
use PSR2PluginBuilder\Services\ClassGenerator;
use PSR2PluginBuilder\Services\ProviderManager;

class GenerateTableCommand extends Command
{
    protected $name;

    protected $class_generator;

    protected $configurations;

    /**
     * @var ProviderManager
     */
    protected $service_provider_manager;

    public function __construct(ClassGenerator $class_generator, Configurations $configurations, ProviderManager $service_provider_manager)
    {
        parent::__construct('table', 'Generate table classes');

        $this->class_generator = $class_generator;
        $this->configurations = $configurations;
        $this->service_provider_manager = $service_provider_manager;

        $this
            ->argument('<name>', 'Name of the table')
            ->argument('<folder>', 'Full path to the table folder')
            // Usage examples:
            ->usage(
            // append details or explanation of given example with ` ## ` so they will be uniformly aligned when shown
                '<bold>  $0</end> <comment>--type common --ball ballon <arggg></end> ## details 1<eol/>' .
                // $0 will be interpolated to actual command name
                '<bold>  $0</end> <comment>-a applet -b ballon <arggg> [arg2]</end> ## details 2<eol/>'
            );
    }

    // When app->handle() locates `init` command it automatically calls `execute()`
    // with correct $ball and $apple values
    public function execute($name, $folder)
    {
        $io = $this->app()->io();

        $class_name = $this->class_generator->snake_to_camel_case($name);
        $class_name = ucfirst($class_name);

        $files = [
            'database/query.php.tpl' => $folder . 'Database/Queries/' . $class_name,
            'database/table.php.tpl' => $folder . 'Database/Tables/' . $class_name,
            'database/row.php.tpl' => $folder . 'Database/Rows/' . $class_name,
            'database/schema.php.tpl' => $folder . 'Database/Schemas/' . $class_name,
        ];

        foreach ($files as $template => $file) {
            $path = $this->class_generator->generate($template, $file, [
                'namespace_database' => $this->configurations->getBaseNamespace() . 'Database',
                'table' => $name,
                'plural' => $name . 's',
                'alias' => $name,
                'date' => (new \DateTime())->format('Ymd')
            ]);

            if( ! $path ) {
                $io->write("The class already exists", true);
                return;
            }

            $io->write("The subscriber is created at this path: $path", true);

        }

        $service_provider_name = $folder . 'Database/ServiceProvider';
        $service_provider_path = $this->class_generator->generate_path( $service_provider_name );
        foreach($files as $file) {
            $this->service_provider_manager->add_class($service_provider_path, $file);
            $this->service_provider_manager->instantiate($service_provider_path, $file);
        }
    }
}