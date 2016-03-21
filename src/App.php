<?php

namespace Pyjac\NaijaEmoji;

class App
{
    /**
     * Stores an instance of the Slim application.
     *
     * @var \Slim\App
     */
    protected $app;

    public function __construct()
    {
        $settings = require __DIR__.'/../src/settings.php';
        $app = new \Slim\App($settings);

        // Set up dependencies
        require __DIR__.'/../src/dependencies.php';

        // Register routes
        require __DIR__.'/../src/routes.php';

        $this->app = $app;
        $this->setUpDatabaseManager();
        $this->setUpDatabaseSchema();
    }

    /**
     * Setup Eloquent ORM.
     */
    private function setUpDatabaseManager()
    {
        // Register the database connection with Eloquent
        $capsule = $this->app->getContainer()->get('capsule');
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

    /**
     * Create necessary database tables needed in the application.
     */
    private function setUpDatabaseSchema()
    {
        try {
            DatabaseSchema::createTables();
        } catch (\Exception $e) {
            // This exception would be caught by the global exception handler.
        }
    }

    /**
     * Get an instance of the application.
     *
     * @return \Slim\App
     */
    public function get()
    {
        return $this->app;
    }
}
