<?php

namespace Elchroy\Lemogis\Controllers\Traits;

trait ReturnJsonTrait
{
    public function returnJSONResponse($response, $message, $code)
    {
        return $response->withJson(['message' => $message], $code);
    }

    public function returnJSONTokenResponse($response, $token)
    {
        return $response->withJson(['token' => $token]);
    }
}