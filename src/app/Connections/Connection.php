<?php

namespace Elchroy\Lemogis\Connections;

use Illuminate\Database\Capsule\Manager as Capsule;
use Elchroy\Lemogis\Exceptions\WrongDatabaseDriverException;

class Connection
{
    public $capsule;

    public function __construct($path = null)
    {
        $this->capsule = new Capsule;

        $configData = $this->loadConfiguration($path);

        $this->capsule->addConnection($configData);

        $this->capsule->bootEloquent();
        $this->capsule->setAsGlobal();

        // Hold a reference to established connection just in case.
        $this->connection = $this->capsule->getConnection('default');
    }

    public function loadConfiguration($path = null)
    {
        $path = $path == null ? __DIR__ . "/../../../config.ini" : $path;
        $config = parse_ini_file($path);
        $driver = $config['driver'];
        if ($driver == 'sqlite') {
            return $this->loadforSQLite($config);
        }
        if ($driver == 'mysql') {
            return $this->loadforMySQL($config);
        }
        else {
            $errorMessage = "Only SQLite and MySQL database are supported at the moment.";
            throw new WrongDatabaseDriverException($errorMessage);
        }
    }

    public function loadforSQLite($config)
    {
        $config['database'] = __DIR__ . '/../../../' . $config['database'];
        return $config;
    }

    public function loadforMySQL($config)
    {
        return $config;
    }

}
