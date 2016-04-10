<?php

namespace Elchroy\Lemogis\Exceptions;

class WrongDatabaseDriverException extends \Exception
{
    public $message;

    public function __construct($message)
    {
        return $this->message = $message;
    }
}