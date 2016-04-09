<?php

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

// $capsule->addConnection([
//     'driver'    => 'mysql',
//     'host'      => 'localhost',
//     'database'  => 'naija',
//     'username'  => 'root',
//     'password'  => '',
//     'charset'   => 'utf8',
//     'collation' => 'utf8_unicode_ci',
//     'prefix'    => '',
// ]);

$capsule->addConnection([
    'driver'    => 'sqlite',
    'host'      => 'localhost',
    'database'  => __DIR__.'/../../../test.sqlite',
    'prefix'    => '',
]);

// $capsule->addConnection([
//       'driver'   => 'sqlite',
//       'database' => __DIR__.'/database.sqlite',
//       'prefix'   => '',
//     ], 'default');



$capsule->setAsGlobal();

$capsule->bootEloquent();


// class Connection {
//   public function __construct()
//   {
//     $this->capsule = new Capsule;
//     // Same as database configuration file of Laravel.
//     $this->capsule->addConnection([
//       'driver'   => 'sqlite',
//       'database' => __DIR__.'/database.sqlite',
//       'prefix'   => '',
//     ], 'default');
//     $this->capsule->bootEloquent();
//     $this->capsule->setAsGlobal();
//     // Hold a reference to established connection just in case.
//     $this->connection = $this->capsule->getConnection('default');
//   }
// }
