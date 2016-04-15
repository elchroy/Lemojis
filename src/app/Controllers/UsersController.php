<?php

namespace Elchroy\Lemojis\Controllers;

use Elchroy\Lemojis\Controllers\Traits\ReturnJsonTrait as ReturnJson;
use Elchroy\Lemojis\Models\LemojisUser as User;

class UsersController
{
    /*
     * Use the return JSON trait.
     */
    use ReturnJson;

    /**
     * Register a user.
     *
     * @param [type] $request  The Slim request object.
     * @param [type] $response The Slim response object.
     *
     * @return [type] Return a 201 message that the user has been created, registered and added to the database.
     *                If the username given is already in the database, then return 409 message relating the conflict issue to the user.
     */
    public function registerUser($request, $response)
    {
        $data = $request->getParsedBody();
        $username = $data['username'];
        $password = $data['password'];

        $this->checkIfUserExists($username, $response);

        if ($this->userExists($username)) {
            return $this->returnJSONResponse($response, 'Username already exists.', 409);
        }

        $this->createUser($username, $password); // Create the new user.

        return $this->returnJSONResponse($response, 'New user has been created successfully.', 201);
    }

    /**
     * Private function to create a user.
     *
     * @param  The username of the user to be created.
     * @param  The password of the user to be created.
     */
    private function createUser($username, $password)
    {
        User::create([
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);
    }

    /**
     * Check if a user does not exist in the database.
     *
     * @param The username of the user to be checked.
     * @param The Slim response object.
     */
    public function checkIfUserDoesNotExist($username, $response)
    {
        return !($this->userExists($username)) ? $this->returnJSONResponse($response, 'Username does not exist.', 409) : '';
    }

    /**
     * Check if th user exists in the database.
     *
     * @param  The slim request object.
     * @param  The slim response object.
     */
    public function checkIfUserExists($username, $response)
    {
        return $this->userExists($username) ? $this->returnJSONResponse($response, 'Username already exists.', 409) : '';
    }

    /**
     * Check if th euser has a token information in the database.
     *
     * @param  The username of the user to check
     *
     * @return [type] TRUE if the user has token. Otherwise false.
     */
    public function userHasToken($username)
    {
        $user = $this->getUser($username);

        return $user->tokenID != null ? true : false;
    }

    /**
     * Check if the user exists in the database.
     *
     * @param  The username of the user to be checked.
     *
     * @return [type] TRUE if the user exists. Otherwise false.
     */
    public function userExists($username)
    {
        $user = $this->getUser($username);

        return $user != null ? true : false;
    }

    /**
     * Get the user given his username.
     *
     * @param  The username of the user to be fetched from the database.
     *
     * @return The user object form the database.
     */
    public function getUser($username)
    {
        return User::where('username', $username)->first();
    }
}
