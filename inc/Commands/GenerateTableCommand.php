<?php

namespace RocketLauncherBuilder\Commands;

use Ahc\Cli\IO\Interactor;
use RocketLauncherBuilder\Entities\Configurations;
use RocketLauncherBuilder\Services\ClassGenerator;
use RocketLauncherBuilder\Services\ProviderManager;

class GenerateTableCommand extends Command
{
    /**
     * Name from the table to generate.
     *
     * @var string
     */
    protected $name;

    /**
     * Base folder for the database files.
     *
     * @var string
     */
    protected $folder;

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
     * Instantiate the class.
     *
     * @param ClassGenerator $class_generator Class generator.
     * @param Configurations $configurations Configuration from the project.
     * @param ProviderManager $service_provider_manager Handle operations with service providers.
     */
    public function __construct(ClassGenerator $class_generator, Configurations $configurations, ProviderManager $service_provider_manager)
    {
        parent::__construct('table', 'Generate table classes');

        $this->class_generator = $class_generator;
        $this->configurations = $configurations;
        $this->service_provider_manager = $service_provider_manager;

        $this
            ->argument('[name]', 'Name of the table')
            ->argument('[folder]', 'Full path to the table folder')
            // Usage examples:
            ->usage(
            // append details or explanation of given example with ` ## ` so they will be uniformly aligned when shown
                '<bold>  $0 table</end> <comment>my_table MyFolder/Path</end> ## Create classes for the table<eol/>'
            );
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
