<?php

// Routes

$app->get('/emojis', "Pyjac\NaijaEmoji\Controller\EmojisController:getEmojis");
$app->get('/emojis/{id}', "Pyjac\NaijaEmoji\Controller\EmojisController:getEmoji");

$app->group('/emojis', function () {

    $this->map(['POST'], '', "Pyjac\NaijaEmoji\Controller\EmojisController:create");

    $this->patch('/{id}', "Pyjac\NaijaEmoji\Controller\EmojisController:updateEmoji");

    $this->put('/{id}', "Pyjac\NaijaEmoji\Controller\EmojisController:updateEmoji");

    $this->delete('/{id}', "Pyjac\NaijaEmoji\Controller\EmojisController:deleteEmoji");

})->add("Pyjac\NaijaEmoji\Middleware\AuthMiddleware");

$app->group('/auth', function () {

    $this->post('/login', "Pyjac\NaijaEmoji\Controller\AuthController:login");
    $this->post('/register', "Pyjac\NaijaEmoji\Controller\AuthController:register");
    $this->post('/logout', "Pyjac\NaijaEmoji\Controller\AuthController:logout")
         ->add("Pyjac\NaijaEmoji\Middleware\AuthMiddleware");
});
