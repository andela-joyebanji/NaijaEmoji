<?php

// Routes

$app->get('/emojis', "Pyjac\NaijaEmoji\Controller\EmojisController:getEmojis");
$app->get('/emojis/{id}', "Pyjac\NaijaEmoji\Controller\EmojisController:getEmoji");

$app->group('/auth', function () {

    $this->post('/login', "Pyjac\NaijaEmoji\Controller\AuthController:login");
    $this->post('/register', "Pyjac\NaijaEmoji\Controller\AuthController:register");
    $this->post('/logout', "Pyjac\NaijaEmoji\Controller\AuthController:logout")
         ->add("Pyjac\NaijaEmoji\Middleware\AuthMiddleware");
});
