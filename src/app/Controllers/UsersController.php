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

        if ($this->userExists($username)) {
            return $this->returnJSONResponse($response, "Username already exists.", 409);
        }

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

    public function userExists($username)
    {
        return $this->getUser($username) ? true : false;
    }

    public function getUser($username)
    {
        return User::where('username', $username)->first();
    }
}