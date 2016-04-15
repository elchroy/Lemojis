<?php

namespace Elchroy\Lemogis\Controllers\Traits;

trait ReturnJsonTrait
{
    /**
     * Return a slim response with the response object as one of the parameters.
     *
     * @param  The Slim response object.
     * @param  The message to be return as part of the message.
     * @param  The code to be used as the response code.
     * @param  The data to be returned if required. This default ot null.
     *
     * @return The returned JSON response.
     */
    public function returnJSONResponse($response, $message, $code, $data = null)
    {
        return $response->withJson(['message' => $message, 'data' => $data], $code);
    }

    /**
     * Return a slim response with the token as part of the message.
     *
     * @param  The Slim response object.
     * @param  The token to be return to the user onse he is logged-in.
     *
     * @return The returned JSON response.
     */
    public function returnJSONTokenResponse($response, $token, $message = null, $code = null)
    {
        return $response->withJson(['message' => $message, 'token' => $token], $code);
    }
}
