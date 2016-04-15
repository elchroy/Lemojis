<?php

/**
 * All the routes to be used in the application.
 */

/**
 * Get all emojis or one emojis given its ID.
 */
$this->group('/emojis', function () {
    $this->get('', 'Elchroy\Lemojis\Controllers\LemojisController:getemojis');
    $this->get('/{id}', 'Elchroy\Lemojis\Controllers\LemojisController:getemoji');
});

/*
 * Grouped Authenticate-Routes.
 */
$this->group('/auth', function () {
    // Register route to register a user.
    $this->post('/register', 'Elchroy\Lemojis\Controllers\UsersController:registerUser');
    // Login route to login the user.
    $this->post('/login', 'Elchroy\Lemojis\Controllers\Authenticate\LemojisAuth:loginUser');
    // Logout a user. Only authenticateed user can access this route.
    $this->get('/logout', 'Elchroy\Lemojis\Controllers\Authenticate\LemojisAuth:logOutUser')
        // The Middleware - Action to be called before each route is called.
        ->add("Elchroy\Lemojis\Controllers\Authenticate\LemojisAuth:verifyToken");
});

/*
 * Grouped emojis Routes. These routes are only accessible to authenticated users.
 */
$this->group('/emojis', function () {
    // Post route to create an emoji.
    $this->post('', 'Elchroy\Lemojis\Controllers\LemojisController:createemoji');
    // Put route to update all the details of an emoji.
    $this->put('/{id}', 'Elchroy\Lemojis\Controllers\LemojisController:updateemoji');
    // Path route to update only some parts of an emoji.
    $this->patch('/{id}', 'Elchroy\Lemojis\Controllers\LemojisController:updateemojiPart');
    // Delete route to delete an emoji from the database.
    $this->delete('/{id}', 'Elchroy\Lemojis\Controllers\LemojisController:deleteemoji');
})
// The Middleware - Action to be called before each route is called.
->add("Elchroy\Lemojis\Controllers\Authenticate\LemojisAuth:verifyToken");

/*
 * Route to the homepage.
 */
$this->get('/', function () {
    // The welcome message for the application.
    echo 'Welcome to Lemoji - A Simple Naija Emoji Service.';
});
