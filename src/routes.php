<?php
// Routes

$app->get('/emojis', "Pyjac\NaijaEmoji\Controller\EmojisController:getEmojis");
$app->get('/emojis/{id}', "Pyjac\NaijaEmoji\Controller\EmojisController:getEmoji");
