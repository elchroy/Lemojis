<?php
require 'vendor/autoload.php';
use Elchroy\Lemogis\LemogisModel;

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'naija',
    'username'  => 'root',
    'password'  => '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);



// $config['displayErrorDetails'] = true;


// use \Psr\Http\Message\ServerRequestInterface as Request;
// use \Psr\Http\Message\ResponseInterface as Response;
// use Elchroy\Lemogis\LemogisModel;

// $app = new \Slim\App(["settings" => $config]);
// $app->get('/hello/{name}', function (Request $request, Response $response) {
    // $name = $request->getAttribute('name');
    // $response->getBody()->write("Hello, $name");

    // return $response;
// });


// $app->get('/', function ($request, $response, $args) {
//     $response->write("Welcome to Slim!");
//     return $response;
// });
//
//
 // require "routes.php";

// $app->get('/emojis', "Elchroy\Lemogis\LemogisController:getEmojis");

LemogisModel::create([
    'name' => "Roy",
    'chars' => "o",
    'keywords' => "These are the keywords",
    'category' => "Categories",
    'date_created' => date("Y m d H-m-s"),
    'date_modified' => date("Y m d H-m-s"),
    'created_by' => "New Person"

]);


// $app->run();