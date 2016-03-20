<?php 

namespace Pyjac\NaijaEmoji;

class App
{
    public  function get()
    {
            $settings = require __DIR__ . '/../src/settings.php';
            $app = new \Slim\App($settings);


            // Set up dependencies
            require __DIR__ . '/../src/dependencies.php';

            // Register routes
            require __DIR__ . '/../src/routes.php';

            // Register the database connection with Eloquent
            $capsule = $app->getContainer()->get('capsule');
            $capsule->setAsGlobal();
            $capsule->bootEloquent();

            try {
                DatabaseSchema::createTables();
            } catch (Exception $e) {
                // This exception would be caught by the global exception handler.
            }
         
        return $app;
    }
}