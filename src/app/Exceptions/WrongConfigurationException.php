<?php

namespace Elchroy\Lemojis\Exceptions;

class WrongConfigurationException extends \Exception
{
    /**
     * Public variable to hold the message to be related to the user.
     */
    public $message;

    /**
     * Construct the message on ocject instantiation and relate to the user.
     *
     * @param The message to be related as a parameter.
     * Return the message to the user.
     */
    public function __construct($message)
    {
        return $this->message = $message;
    }
}
