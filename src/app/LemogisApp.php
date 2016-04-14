<?php

namespace Elchroy\Lemogis;

use Slim\App as Slim;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Illuminate\Database\Capsule\Manager as Capsule;
use Elchroy\Lemogis\Connections\Connection;

class LemogisApp extends Slim
{
    /**
     * Public variable to hold some settings for the user.
     */
    public $config = [];

    /**
     * Construct the object with the given Connection class, which defaults to null.
     * @param Connection class object, which defaults to null.
     */
    public function __construct(Connection $connection = null)
    {
        $connection = $connection == null ? new Connection : $connection;

        $this->config['displayErrorDetails'] = true;

        parent::__construct(["settings" => $this->config]);

        $this->loadRoutes();
    }

    /**
     * Load all the routes from the routes file.
     */
    private function loadRoutes()
    {
        require('Routes/routes.php');
    }
}