<?php

namespace Elchroy\Lemogis;

use Slim\App as Slim;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Illuminate\Database\Capsule\Manager as Capsule;
use Elchroy\Lemogis\Connections\Connection;

class LemogisApp extends Slim
{
    public $config = [];

    public function __construct(Connection $connection = null)
    {
        $connection = $connection == null ? new Connection : $connection;

        $this->config['displayErrorDetails'] = true;

        parent::__construct(["settings" => $this->config]);

        $this->db = function () {
            return new Capsule;
        };

        $this->loadRoutes();
    }

    private function loadRoutes()
    {
        require('Routes/routes.php');
    }
}