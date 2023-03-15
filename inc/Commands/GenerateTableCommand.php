<?php

namespace RocketLauncherBuilder\Commands;

use Ahc\Cli\IO\Interactor;
use RocketLauncherBuilder\Entities\Configurations;
use RocketLauncherBuilder\Services\ClassGenerator;
use RocketLauncherBuilder\Services\ProjectManager;
use RocketLauncherBuilder\Services\ProviderManager;

/**
 * @property string|null $name Name from the table to generate.
 * @property string|null $folder Base folder for the database files.
 */
class GenerateTableCommand extends Command
{
    /**
     * Class generator.
     *
     * @var ClassGenerator
     */
    protected $class_generator;

    /**
     * Configuration from the project.
     *
     * @var Configurations
     */
    protected $configurations;

    /**
     * Handle operations with service providers.
     *
     * @var ProviderManager
     */
    protected $service_provider_manager;

    /**
     * @var ProjectManager
     */
    protected $project_manager;

    /**
     * Instantiate the class.
     *
     * @param ClassGenerator $class_generator Class generator.
     * @param Configurations $configurations Configuration from the project.
     * @param ProviderManager $service_provider_manager Handle operations with service providers.
     */
    public function __construct(ClassGenerator $class_generator, Configurations $configurations, ProviderManager $service_provider_manager, ProjectManager $project_manager)
    {
        parent::__construct('table', 'Generate table classes');

        $this->class_generator = $class_generator;
        $this->configurations = $configurations;
        $this->service_provider_manager = $service_provider_manager;
        $this->project_manager = $project_manager;

        $this
            ->argument('[name]', 'Name of the table')
            ->argument('[folder]', 'Full path to the table folder')
            // Usage examples:
            ->usage(
            // append details or explanation of given example with ` ## ` so they will be uniformly aligned when shown
                '<bold>  $0 table</end> <comment>my_table MyFolder/Path</end> ## Create classes for the table<eol/>'
            );

        if($this->project_manager->is_resolver_present()) {
            $this->option('-p --provider', 'Type from the provider');
        }
    }

    /**
     * Interacts with the user to get missing values.
     *
     * @param Interactor $io Interface to interact with the user.
     *
     * @return void
     */
    public function interact(Interactor $io)
    {
        // Collect missing opts/args
        if (!$this->name) {
            $this->set('name', $io->prompt('Enter name from the table'));
        }

        if (!$this->folder) {
            $this->set('folder', $io->prompt('Enter folder from database classes'));
        }
    }

    /**
     * Execute the command.
     *
     * @param string|null $name Name from the table to generate.
     * @param string|null $folder Base folder for the database files.
     *
     * @return void
     */
    public function execute($name, $folder, $provider)
    {
        $io = $this->app()->io();

        $class_name = $this->class_generator->snake_to_camel_case($name);
        $class_name = ucfirst($class_name);

        if(! $provider) {
            $provider = '';
        }

        $files = [
            'database/query.php.tpl' => trim($folder, '/') . '/Database/Queries/' . $class_name,
            'database/table.php.tpl' => trim($folder, '/') . '/Database/Tables/' . $class_name,
            'database/row.php.tpl' => trim($folder, '/') . '/Database/Rows/' . $class_name,
            'database/schema.php.tpl' => trim($folder, '/') . '/Database/Schemas/' . $class_name,
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

            $io->write("The class is created at this path: $path", true);

        }

        $service_provider_name = trim($folder, '/') . '/Database';

        $this->service_provider_manager->maybe_generate_service_provider($service_provider_name . DIRECTORY_SEPARATOR . 'ServiceProvider', $provider);

        foreach($files as $template => $file) {
            if($template === 'database/table.php.tpl') {
                $this->service_provider_manager->instantiate($service_provider_name, $file);
                continue;
            }
            $this->service_provider_manager->add_class($service_provider_name, $file);
        }
    }
}
