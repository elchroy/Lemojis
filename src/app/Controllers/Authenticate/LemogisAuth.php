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

        if (!($this->isCorrectDetails($username, $password))) {
            return $this->returnJSONResponse($response, "Incorrect username or password", 404);
        }

        $user = $this->controller->getUser($username);

        $this->saveTokenForLogout(NULL, $username);

        $tokenTime = ($request->getAttribute('TokenTime') == null) ? time() : $request->getAttribute('TokenTime');

        $tokenResponse = $this->returnJSONTokenResponse($response, $this->createToken($username, $tokenTime));
        return $tokenResponse;
    }

    private function isCorrectDetails($givenusername, $givenPassword)
    {
        $user = $this->controller->getUser($givenusername);
        if ($user == null) {
            return false;
        }
        $userInfo = $user->toArray();
        return password_verify($givenPassword, $userInfo['password']);
    }

    private function createToken($username, $time = null)
    {
        $time = $time == null ? time() : $time;
        $tokenId = base64_encode($time);
        $issuedAt = $time;
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
        if (!($request->hasHeader('authorization'))) {
            return $this->returnJSONResponse($response, "Bad Request - Token not found in request. Please Login", 400);
        }

        $authHeader = $request->getHeader('authorization')[0];
        list($token) = sscanf($authHeader, '%s');

        if (!$token) {
            return $this->returnJSONResponse($response, "Please Provide Token From Login", 400);
        }

        if ($this->isExpired($token))
        {
            return $this->returnJSONResponse($response, "Token is Expired. Please re-login.", 405);
        }

        $jd = $this->getDecodeInfo();
        $secretKey = base64_decode($jd['APP_SECRET']);
        $signature = $jd['APP_SIGNATURE'];

        $decodedToken = JWT::decode($token, $secretKey, [$signature]);
        if ($decodedToken == false) {
            return $this->returnJSONResponse($response, "Unauthorized", 401);
        }
        $username = ($decodedToken->data->username);

        if ($this->controller->userHasToken($username)) {
            return $this->returnJSONResponse($response, "Please Re-login.", 405);
        }

        $this->controller->checkIfUserDoesNotExist($username, $response);
        $userInfo = $this->controller->getUser($username)->toArray();

        $tokenID = $this->getTokenID($token);
        $storeToken = json_encode([$tokenID, $username]);
        $request = $request->withAttribute('StoreToken', $storeToken);
        $response = $next($request, $response);
        return $response;
    }

    private function decodeToken($token, $path = null)
    {
        $jd = $this->getDecodeInfo($path);
        $secretKey = base64_decode($jd['APP_SECRET']);
        $signature = $jd['APP_SIGNATURE'];
        try {
            return $decodedToken = JWT::decode($token, $secretKey, [$signature]);
        } catch (Exception $e) {
            return false;
        }
    }

    private function getDecodeInfo($path = null)
    {
        $path = $path == null ? __DIR__ . '/../../../../.jwt' : $path;
        return $jwtInfo = parse_ini_file($path);
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