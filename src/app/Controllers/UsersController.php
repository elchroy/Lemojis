<?php

namespace Elchroy\Lemogis\Controllers;

use Elchroy\Lemogis\Models\LemogisUser as User;
use Elchroy\Lemogis\Controllers\Traits\ReturnJsonTrait as ReturnJson;

class UsersController
{
    use ReturnJson;

    public function registerUser($request, $response)
    {
        $data = $request->getParsedBody();
        $username = $data['username'];
        $password = $data['password'];

        $this->checkIfUserExists($username, $response);

        // if ($this->userExists($username)) {
        //     return $this->returnJSONResponse($response, "Username already exists.", 409);
        // }

        $this->createUser($username, $password); // Create the new user.

        return $this->returnJSONResponse($response, 'New user has been created successfully.', 201);
    }

    private function createUser($username, $password)
    {
        User::create([
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);
    }

    public function checkIfUserDoesNotExist($username, $response)
    {
        return !($this->userExists($username)) ? $this->returnJSONResponse($response, "Username does not exist.", 409) : '';
    }

    public function checkIfUserExists($username, $response)
    {
        return $this->userExists($username) ? $this->returnJSONResponse($response, "Username already exists.", 409) : '';
    }

    public function userHasToken($username)
    {
        $user = $this->getUser($username);
        return $user->tokenID != null ? true : false;
    }

    public function userExists($username)
    {
        $user = $this->getUser($username);
        return $user != null ? true : false;
    }

    public function getUser($username)
    {
        return User::where('username', $username)->first();
    }
}