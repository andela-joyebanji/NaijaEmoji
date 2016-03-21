<?php

// DIC configuration

$container = $app->getContainer();

$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
         $r = $response
            ->withStatus(404)
            ->withHeader('Content-Type', 'text/html')
            ->write('Page not found');

        return $r;
    };
};

// Database
$container['capsule'] = function ($c) {
    $capsule = new Illuminate\Database\Capsule\Manager();
    $neededValues = ['driver', 'host', 'username', 'password', 'charset', 'collation', 'database', 'port'];
    //Extract needed environment variables from the $_ENV global array
    $config = array_intersect_key($_SERVER, array_flip($neededValues));
    $capsule->addConnection($config);

    return $capsule;
};

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];

    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::DEBUG));

    return $logger;
};

$container['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {

        return $response->withJson(['message' => 'Something went wrong!'], 500);
    };
};

if (getenv('APP_ENV') !== 'production') {
    $dotenv = new \Dotenv\Dotenv(__DIR__.'/../');
    $dotenv->overload();
}
