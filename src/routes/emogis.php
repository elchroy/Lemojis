<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->group('/emogis', function () use ($app) {
    $app->get('', 'Elchroy\Lemogis\Controllers\LemogisController:getEmogis')->setName('getEmogis');
    $app->get('/{id}', 'Elchroy\Lemogis\Controllers\LemogisController:getEmogi')->setName('getEmogi');
});

$app->group('/auth', function () use ($app) {
    $app->post('/register', 'Elchroy\Lemogis\Controllers\UsersController:registerUser')->setName('registerUser');
    $app->post('/login', 'Elchroy\Lemogis\Controllers\Authenticate\LemogisAuth:loginUser')->setName('loginUser');
    $app->get('/logout', 'Elchroy\Lemogis\Controllers\Authenticate\LemogisAuth:logOutUser')
        ->add("Elchroy\Lemogis\Controllers\Authenticate\LemogisAuth:verifyToken");
});

$app->group('/emogis', function () use ($app) {
    $app->post('', 'Elchroy\Lemogis\Controllers\LemogisController:createEmogi')->setName('createEmogi');
    $app->put('/{id}', 'Elchroy\Lemogis\Controllers\LemogisController:updateEmogi')->setName('updateEmogi');
    $app->patch('/{id}', 'Elchroy\Lemogis\Controllers\LemogisController:updateEmogiPart')->setName('patchUpdate');
    $app->delete('/{id}', 'Elchroy\Lemogis\Controllers\LemogisController:deleteEmogi')->setName('deleteEmogi');
})->add("Elchroy\Lemogis\Controllers\Authenticate\LemogisAuth:verifyToken");
