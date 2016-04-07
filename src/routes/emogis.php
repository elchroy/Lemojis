<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/emogis', 'Elchroy\Lemogis\Controllers\LemogisController:getEmogis');

$app->get('/emogis/{id}', 'Elchroy\Lemogis\Controllers\LemogisController:getEmogi');

$app->post('/auth/register', 'Elchroy\Lemogis\Controllers\UsersController:registerUser');

$app->post('/auth/login', 'Elchroy\Lemogis\Controllers\Authenticate\LemogisAuth:loginUser');

$app->group('/emogis', function () use ($app) {
    $app->post('', 'Elchroy\Lemogis\Controllers\LemogisController:createEmogi');
    $app->put('/{id}', 'Elchroy\Lemogis\Controllers\LemogisController:updateEmogi');
    $app->patch('/{id}', 'Elchroy\Lemogis\Controllers\LemogisController:updateEmogiPart');
    $app->delete('/{id}', 'Elchroy\Lemogis\Controllers\LemogisController:deleteEmogi');
})->add("Elchroy\Lemogis\Controllers\Authenticate\LemogisAuth:verifyToken");
