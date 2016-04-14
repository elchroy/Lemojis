<?php

/**
 * All the routes to be used in the application.
 */

/**
 * Get all emogis or one emogis given its ID.
 */
$this->group('/emogis', function () {
    $this->get('', 'Elchroy\Lemogis\Controllers\LemogisController:getEmogis');
    $this->get('/{id}', 'Elchroy\Lemogis\Controllers\LemogisController:getEmogi');
});

/**
 * Grouped Authenticate-Routes.
 */
$this->group('/auth', function () {
    // Register route to register a user.
    $this->post('/register', 'Elchroy\Lemogis\Controllers\UsersController:registerUser');
    // Login route to login the user.
    $this->post('/login', 'Elchroy\Lemogis\Controllers\Authenticate\LemogisAuth:loginUser');
    // Logout a user. Only authenticateed user can access this route.
    $this->get('/logout', 'Elchroy\Lemogis\Controllers\Authenticate\LemogisAuth:logOutUser')
        // The Middleware - Action to be called before each route is called.
        ->add("Elchroy\Lemogis\Controllers\Authenticate\LemogisAuth:verifyToken");
});

/**
 * Grouped Emogis Routes. These routes are only accessible to authenticated users.
 */
$this->group('/emogis', function () {
    // Post route to create an Emogi.
    $this->post('', 'Elchroy\Lemogis\Controllers\LemogisController:createEmogi');
    // Put route to update all the details of an emogi.
    $this->put('/{id}', 'Elchroy\Lemogis\Controllers\LemogisController:updateEmogi');
    // Path route to update only some parts of an emogi.
    $this->patch('/{id}', 'Elchroy\Lemogis\Controllers\LemogisController:updateEmogiPart');
    // Delete route to delete an emogi from the database.
    $this->delete('/{id}', 'Elchroy\Lemogis\Controllers\LemogisController:deleteEmogi');
})
// The Middleware - Action to be called before each route is called.
->add("Elchroy\Lemogis\Controllers\Authenticate\LemogisAuth:verifyToken");


/**
 * Route to the homepage.
 */
$this->get('/', function() {
    // The welcome message for the application.
    echo 'Welcome to Lemogi - A Simple Naija Emoji Service.';
});