<?php

require '../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

require 'connection.php';

$config['displayErrorDetails'] = true;

// $app = new \Slim\App();
$app = new \Slim\App(["settings" => $config]);

$app->db = function () {
    return new Capsule;
};

require 'routes/emogis.php';