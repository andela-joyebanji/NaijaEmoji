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

        if ($exception instanceof \DomainException || $exception instanceof \Firebase\JWT\SignatureInvalidException) {
            return $response->withJson(['message' => $exception->getMessage()], 401);
        }
        if ($exception instanceof \Firebase\JWT\ExpiredException) {
            return $response->withJson(['message' => 'The provided token as expired.'], 401);
        }

        // Refernece: http://stackoverflow.com/questions/3825990/http-response-code-for-post-when-resource-already-exists
        if ($exception instanceof Pyjac\NaijaEmoji\Exception\DuplicateEmojiException) {
            return $response->withJson(['message' => $exception->getMessage()], 409);
        }

        if ($exception instanceof \InvalidArgumentException || $exception instanceof \UnexpectedValueException) {
            return $response->withJson(['message' => $exception->getMessage()], 400);
        }

        $c->logger->critical($exception->getMessage());
        return $response->withJson(['message' => "Sorry, We're having technical difficulties processing your request. Our Developers would fix this issue as soon as possible."], 500);
    };
};

if (getenv('APP_ENV') !== 'production') {
    
    if ($envFilePath === "") {
        $envFilePath = __DIR__.'/../';
    }
    $dotenv = new \Dotenv\Dotenv($envFilePath);
    $dotenv->overload();
}
