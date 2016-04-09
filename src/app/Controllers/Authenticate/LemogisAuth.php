<?php

namespace Elchroy\Lemogis\Controllers\Authenticate;

use Elchroy\Lemogis\Controllers\LemogisController;
use Elchroy\Lemogis\Controllers\UsersController;
use Elchroy\Lemogis\Models\LemogisUser as User;
use Elchroy\Lemogis\Controllers\Traits\ReturnJsonTrait as ReturnJson;
use Firebase\JWT\JWT;

class LemogisAuth
{
    use ReturnJson;

    public $controller;

    public function __construct()
    {
        $this->controller = new UsersController();
    }

    public function loginUser($request, $response)
    {
        $data = $request->getParsedBody();
        $username = $data['username'];
        $password = $data['password'];

        if (!($this->controller->userExists($username))) {
            return $this->returnJSONResponse($response, "Username does not exist.", 404);
        }

        $user = $this->controller->getUser($username);

        $userInfo = $user->toArray();

        if (!(password_verify($password, $userInfo['password']))) {
            return $this->returnJSONResponse($response, "Password Incorrect.", 403);
        }

        // if ($this->isLoggedIn($user)) {
        //     return $this->returnJSONResponse($response, "Already Logged In.", 403);
        // }

        $this->saveTokenForLogout('LoggedIn', $username);

        $tokenResponse = $this->returnJSONTokenResponse($response, $this->createToken($username));
        return $tokenResponse;
    }

    private function isLoggedIn($user)
    {
        return $user->tokenID === 'LoggedIn';
    }



    private function createToken($username)
    {
        $tokenId = base64_encode(time());
        $issuedAt = time();
        $notBefore  = $issuedAt + 10;
        $expire     = $notBefore + 2000;
        $secretKey = base64_decode('sampleSecret'); // or get the app key from the config file.
        $JWTToken = [
            'iat'  => $issuedAt,
            'jti'  => $tokenId,
            'nbf'  => $notBefore,
            'exp'  => $expire,
            'data' => ['username' => $username],
        ];

        $jwt = JWT::encode(
            $JWTToken,      //Data to be encoded in the JWT
            $secretKey, // The signing key
            'HS512'     // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
        );

        return $jwt;
    }

    public function logOutUser($request, $response)
    {
        $storeInfo = $request->getAttribute('StoreToken');
        $storeInfo = json_decode($storeInfo);
        $this->saveTokenForLogout($storeInfo[0], $storeInfo[1]);
        return $this->returnJSONResponse($response, "Successfully Logged Out", 200);
    }

    private function saveTokenForLogout($token, $username)
    {
        $user = $this->controller->getUser($username);
        $user->tokenID = $token;
        $user->save();
    }
    public function verifyToken($request, $response, $next)
    {
        $this->checkRequestType($request, $response);
        $this->checkRequestHeader($request, $response);

        $authHeader = $request->getHeader('authorization')[0];
        list($jwt) = sscanf($authHeader, '%s');

        $this->checkTokenIsFound($jwt, $response);
        $this->checkExpiredToken($jwt, $response);
        $decodedToken = $this->tryDecodingToken($jwt, $response);
        $username = ($decodedToken->data->username);

        $this->checkLoggedOutuser($username, $response);
        $this->controller->checkIfUserDoesNotExist($username, $response);
        $userInfo = $this->controller->getUser($username)->toArray();

        $tokenID = $this->getTokenID($jwt);
        $storeToken = json_encode([$tokenID, $username]);
        $request = $request->withAttribute('StoreToken', $storeToken);
        $response = $next($request, $response);
        return $response;
    }

    private function tryDecodingToken($token, $response)
    {
        try {
            $secretKey = base64_decode('sampleSecret');
            return $decodedToken = JWT::decode($token, $secretKey, ['HS512']);
        } catch (Exception $e) {
             // the token was not able to be decoded. This is likely because the signature was not able to be verified (tampered token)
            header('HTTP/1.0 401 Unauthorized');
            return $this->returnJSONResponse($response, "Unauthorized", 401);
        }
    }

    private function checkLoggedOutuser($username, $response)
    {
        if ($this->controller->userHasToken($username)) {
            return $this->returnJSONResponse($response, "You have already logged out. Please re-login.", 405);
        }
    }

    private function checkExpiredToken($token, $response)
    {
        if ($this->isExpired($token))
        {
            return $this->returnJSONResponse($response, "Token is Expired. Please re-login.", 405);
        }
    }

    private function checkTokenIsFound($token, $response)
    {
        if (!$token) {
            header('HTTP/1.0 400 Bad Request');
            return $this->returnJSONResponse($response, "Token not found in request", 400);
        }
    }

    private function checkRequestHeader($request, $response)
    {
        if (!($request->hasHeader('authorization'))) {
            // header('HTTP/1.0 400 Bad Request');
            $exceptionMessage = $this->returnJSONResponse($response, "Bad Request - Token not found in request", 400);
            throw new \Exception($exceptionMessage);
        }
    }

    private function checkRequestType($request, $response)
    {
        if (!$request->isPost()) {
            header('HTTP/1.0 405 Method Not Allowed');
            return $this->returnJSONResponse($response, "Method Not Allowed", 405);
        }
    }

    private function isExpired($token)
    {
        return $this->expirationDateOf($token) < time();
    }

    private function expirationDateOf($token)
    {
        // Get the expiration date of the token
        return $this->getToken($token)->exp;
    }

    private function getToken($token)
    {
        return json_decode(base64_decode(explode('.', $token)[1]));
    }

    private function getTokenID($token)
    {
        return $this->getToken($token)->jti;
    }

}