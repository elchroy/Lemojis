<?php

// The index file where to serve the application from.

require_once '../vendor/autoload.php';

$app = new Elchroy\Lemogis\LemogisApp();

$app->run();
