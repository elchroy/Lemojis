<?php

$this->group('/emogis', function () {
    $this->get('', 'Elchroy\Lemogis\Controllers\LemogisController:getEmogis');
    $this->get('/{id}', 'Elchroy\Lemogis\Controllers\LemogisController:getEmogi');
});

$this->group('/auth', function () {
    $this->post('/register', 'Elchroy\Lemogis\Controllers\UsersController:registerUser');
    $this->post('/login', 'Elchroy\Lemogis\Controllers\Authenticate\LemogisAuth:loginUser');
    $this->get('/logout', 'Elchroy\Lemogis\Controllers\Authenticate\LemogisAuth:logOutUser')
        ->add("Elchroy\Lemogis\Controllers\Authenticate\LemogisAuth:verifyToken");
});

$this->group('/emogis', function () {
    $this->post('', 'Elchroy\Lemogis\Controllers\LemogisController:createEmogi');
    $this->put('/{id}', 'Elchroy\Lemogis\Controllers\LemogisController:updateEmogi');
    $this->patch('/{id}', 'Elchroy\Lemogis\Controllers\LemogisController:updateEmogiPart');
    $this->delete('/{id}', 'Elchroy\Lemogis\Controllers\LemogisController:deleteEmogi');
})->add("Elchroy\Lemogis\Controllers\Authenticate\LemogisAuth:verifyToken");


$this->get('/', function() {
    echo 'Welcome to Lemogi - A Simple Naija Emoji Service.';
});