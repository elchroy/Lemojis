<?php

namespace Elchroy\Lemojis\Controllers\Authenticate;

use Elchroy\Lemojis\Controllers\Traits\ReturnJsonTrait as ReturnJson;
use Elchroy\Lemojis\Controllers\UsersController;
use Elchroy\Lemojis\Controllers\LemojisController;
use Elchroy\Lemojis\Models\LemojisUser as User;
use Firebase\JWT\JWT;

class LemojisAuth
{
    /*
     * User the return JSON traits.
     */
    use ReturnJson;

    /**
     * Public variable to hold the user controller object.
     */
    public $controller;

    /**
     * Constructor to set the public controller as a user controller object.
     */
    public function __construct()
    {
        $this->controller = new UsersController();
    }

    /**
     * Login a user. And give him a token.
     *
     * @param  The slim request object.
     * @param  The slim response object.
     *
     * @return Return the JSON encoded token to be assigned to the user.
     *                If the given username does not exist or the given password does not match the username,
     *                a JSON decoded 404 message is returned.
     */
    public function loginUser($request, $response)
    {
        $data = $request->getParsedBody();
        $username = $data['username'];
        $password = $data['password'];

        if (!($this->isCorrectDetails($username, $password))) {
            return $this->returnJSONResponse($response, 'Incorrect username or password', 404);
        }

        $user = $this->controller->getUser($username);

        $this->saveTokenForLogout(null, $username);

        $tokenTime = ($request->getAttribute('TokenTime') == null) ? time() : $request->getAttribute('TokenTime');

        $tokenResponse = $this->returnJSONTokenResponse($response, $this->createToken($username, $tokenTime), 'Logged In Successfully', 200);

        return $tokenResponse;#.'Edit this message and return a json enhanced message';
    }

    /**
     * Private function to check if the given user details are valid.
     *
     * @param  Username to validate.
     * @param  Password to validate.
     *
     * @return true if all checks are valid. FALSE otherwise.
     */
    private function isCorrectDetails($givenusername, $givenPassword)
    {
        $user = $this->controller->getUser($givenusername);
        if ($user == null) {
            return false;
        }
        $userInfo = $user->toArray();

        return password_verify($givenPassword, $userInfo['password']);
    }

    /**
     * Private function to create a token for the user.
     *
     * @param  The user's username.
     * @param  The time to be used to create the token.
     *
     * @return The created token as a string.
     */
    private function createToken($username, $time = null)
    {
        $time = $time == null ? time() : $time;
        $tokenId = base64_encode($time);
        $issuedAt = $time;
        $notBefore = $issuedAt + 10;
        $expire = $notBefore + 2000;
        $JWTToken = [
            'iat'  => $issuedAt,
            'jti'  => $tokenId,
            'nbf'  => $notBefore,
            'exp'  => $expire,
            'data' => ['username' => $username],
        ];

        $jd = $this->getDecodeInfo();
        $secretKey = base64_decode($jd['APP_SECRET']);
        $signature = $jd['APP_SIGNATURE'];
        $jwt = JWT::encode($JWTToken, $secretKey, $signature);

        return $jwt;
    }

    /**
     * Log a user out of the Application.
     *
     * @param  A slim request object.
     * @param  A slim repsonse object.
     *
     * @return A JSON decoded 200 message that the user has been logged out.
     */
    public function logOutUser($request, $response)
    {
        $storeInfo = $request->getAttribute('StoreToken');
        $storeInfo = json_decode($storeInfo);
        $this->saveTokenForLogout($storeInfo[0], $storeInfo[1]);

        return $this->returnJSONResponse($response, 'Successfully Logged Out', 200);
    }

    /**
     * Private function to save the token for the user upon looging out of the application.
     *
     * @param  The token to be save against the user.
     * @param  The username of the user to log out.
     */
    private function saveTokenForLogout($token, $username)
    {
        $user = $this->controller->getUser($username);
        $user->tokenID = $token;
        $user->save();
    }

    /**
     * Verify user status and control access to private routes.
     *
     * @param  A slim request object.
     * @param  A slim repsonse object.
     * @param  The next function to call if the verifications are performed and the user is authorized.
     */
    public function verifyToken($request, $response, $next)
    {
        // If there is no authorization header in the request object, the return 400 status json encoded message.
        if (!($request->hasHeader('authorization'))) {
            return $this->returnJSONResponse($response, 'Bad Request - Token not found in request. Please Login', 400);
        }

        $authHeader = $request->getHeader('authorization')[0];
        list($token) = sscanf($authHeader, '%s');

        /*
         * If there is no token in the authorization header, then return 400 code json encoded message.
         */
        if (!$token) {
            return $this->returnJSONResponse($response, 'Please Provide Token From Login', 404);
        }

        /*
         * If the token that is in the header is expired, the return a JSON encoded 405 message.
         */
        if ($this->isExpired($token)) {
            return $this->returnJSONResponse($response, 'Token is Expired. Please re-login.', 401);
        }

        $jd = $this->getDecodeInfo();
        $secretKey = base64_decode($jd['APP_SECRET']);
        $signature = $jd['APP_SIGNATURE'];

        $decodedToken = $this->decodeToken($token);

        $username = ($decodedToken->data->username);

        if ($request->isPut() || $request->isPatch() || $request->isDelete()) {
            $request = $request->withAttribute('LoggedUser', $username);
        }
        /*
         * If the user already has a token value as one of his properties, then return a message that the user has logged out. The message is JSON encoded.
         */
        if ($this->controller->userHasToken($username)) {
            return $this->returnJSONResponse($response, 'Please Re-login.', 401);
        }

        $this->controller->checkIfUserDoesNotExist($username, $response);
        $userInfo = $this->controller->getUser($username)->toArray();

        $tokenID = $this->getTokenID($token);
        $storeToken = json_encode([$tokenID, $username]);
        $request = $request->withAttribute('StoreToken', $storeToken);
        $response = $next($request, $response);

        return $response;
    }

    /**
     * Decode the token.
     * The decoding signatures or secrets are loaded.
     *
     * @param  The token to be decoded.
     *
     * @return Return the decoded token.
     */
    private function decodeToken($token)
    {
        $jd = $this->getDecodeInfo();
        $secretKey = base64_decode($jd['APP_SECRET']);
        $signature = $jd['APP_SIGNATURE'];

        return $decodedToken = JWT::decode($token, $secretKey, [$signature]);
    }

    /**
     * The the decoding signatures ot secrets.
     *
     * @param  The path to the jwt file where the decoding information have been stored.
     *
     * @return An array containing the decoding keys.
     */
    private function getDecodeInfo($path = null)
    {
        $path = $path == null ? __DIR__.'/../../../../public/.jwt' : $path;

        return parse_ini_file($path);
    }

    /**
     * Check if the token is expired.
     *
     * @param  The token to be checked.
     *
     * @return true if teh token is expired. FALSE otherwise.
     */
    private function isExpired($token)
    {
        return $this->expirationDateOf($token) < time();
    }

    /**
     * Get the expiration date of the token.
     *
     * @param  The token whose expiration date is required.
     *
     * @return The expiration date of the token.
     */
    private function expirationDateOf($token)
    {
        // Get the expiration date of the token
        return $this->getToken($token)->exp;
    }

    /**
     * Get the token header.
     *
     * @param  The token whose header is required.
     *
     * @return The header of the token.
     */
    private function getToken($token)
    {
        return json_decode(base64_decode(explode('.', $token)[1]));
    }

    /**
     * Get the token ID form the token header.
     *
     * @param  The token whose ID is required.
     *
     * @return The token ID from header of the token.
     */
    private function getTokenID($token)
    {
        return $this->getToken($token)->jti;
    }
}
