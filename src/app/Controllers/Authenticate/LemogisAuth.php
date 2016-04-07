<?php

namespace Elchroy\Lemogis\Controllers\Authenticate;

use Elchroy\Lemogis\Controllers\LemogisController;
use Elchroy\Lemogis\Controllers\UsersController;
use Elchroy\Lemogis\Models\LemogisUser;
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

        $userInfo = $this->controller->getUser($username)->toArray();

        if (!(password_verify($password, $userInfo['password']))) {
            return $this->returnJSONResponse($response, "Password Incorrect.", 403);
        }

        $tokenResponse = $this->returnJSONTokenResponse($response, $this->createToken($username));
        return $tokenResponse;
        // var_dump($request->getHeaderLine('Accept'));
    }



    public function loginUserd()
    {
        $secretKey = base64_decode('sampleSecret');
    }

    private function createToken($username)
    {
        $tokenId = base64_encode(time());
        $issuedAt = time();
        $notBefore  = $issuedAt + 10;
        $expire     = $notBefore + 60;
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


    public function __invoke($request, $response, $next)
    {
        if (!$request->hasHeader('authorization')) {
            throw new \UnexpectedValueException('Token not provided');
        }
        $userJwt = $this->getUserToken($request);
        $jwtToken = JWT::decode($userJwt, getenv('APP_SECRET'), [getenv('JWT_ALGORITHM')]);
        $user = User::with('blacklistedTokens')->where('id', $jwtToken->data->userId)->first();
        if ($user->blacklistedTokens()->where('token_jti', $jwtToken->jti)->get()->first()) {
            throw new \DomainException('Your token has been logged out.');
        }
        $request = $request->withAttribute('user', $user);
        $request = $request->withAttribute('token_jti', $jwtToken->jti);
        return $next($request, $response);
    }
    public function getUserToken($request)
    {
        // Get the authorization header value in other to retrieve the token
        $authHeader = $request->getHeader('authorization');
        list($userJwt) = sscanf($authHeader[0], 'Bearer %s');
        if (!$userJwt) {
            throw new \UnexpectedValueException('Token not provided');
        }
        return $userJwt;
    }


    public function verifyToken($request, $response, $next)
    {
        if (!($request->hasHeader('authorization'))) {
            return $this->returnJSONResponse($response, "Token not found", 404);
        }
    }

}