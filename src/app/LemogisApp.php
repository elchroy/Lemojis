<?php

namespace Elchroy\Lemogis;

use Slim\App as Slim;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Illuminate\Database\Capsule\Manager as Capsule;

class LemogisApp extends Slim
{
    public $config = [];

    public function __construct($userSettings = [])
    {
        $this->loadConnection();
        $this->config['displayErrorDetails'] = true;

        parent::__construct(["settings" => $this->config]);
        // parent::__construct(["settings" => $userSettings]);

        $this->db = function () {
            return new Capsule;
        };

        $this->loadRoutes();

        $this->get('/hello/{name}', function ($request, $response) {
            $name = $request->getAttribute('name');
            $response->getBody()->write("Hello, $name");
            return $response;
        });

        // $this->get('/emogis', 'Elchroy\Lemogis\Controllers\LemogisController:getEmogis');

        // $this->get('/', function($request, $response){
        //     $this->get('', 'Elchroy\Lemogis\Controllers\LemogisController:getEmogis');
        // });

        // $this->group('/emogis', function () {
        //     $this->get('', 'Elchroy\Lemogis\Controllers\LemogisController:getEmogis')->setName('getEmogis');
        //     $this->get('/{id}', 'Elchroy\Lemogis\Controllers\LemogisController:getEmogi')->setName('getEmogi');
        // });

    }

    public function loadConnection()
    {
        require_once('Connections/connection.php');
    }

    // pp

    private function loadRoutes()
    {
        require('Routes/routes.php');
    }

    /**
     * @return \Slim\Http\Response
     */
    public function invoke()
    {
        // $this->middleware[0]->call();
        // $this->response()->finalize();
        // return $this->response();
        return $this->run();
    }
}