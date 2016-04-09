<?php

namespace Elchroy\Lemogis\Controllers\Traits;

trait ReturnJsonTrait
{
    public function returnJSONResponse($response, $message, $code, $data = NULL)
    {
        return $response->withJson(['message' => $message, 'data' => $data], $code);
    }

    public function returnJSONTokenResponse($response, $token)
    {
        return $response->withJson(['token' => $token]);
    }
}