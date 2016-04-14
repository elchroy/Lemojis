<?php

namespace Elchroy\Lemogis\Connections;

use Illuminate\Database\Capsule\Manager as Capsule;
use Elchroy\Lemogis\Exceptions\WrongDatabaseDriverException;

class Connection
{
    public $capsule;

    public function __construct($configData = null)
    {
        $this->capsule = new Capsule;

        $configData = $configData == null ? $this->loadConfiguration() : $configData;

        $this->capsule->addConnection($configData);

        $this->capsule->bootEloquent();
        $this->capsule->setAsGlobal();

        // Hold a reference to established connection just in case.
        $this->connection = $this->capsule->getConnection('default');
    }

    private function loadConfiguration()
    {
        $config = parse_ini_file(__DIR__ . "/../../../config.ini");
        $driver = $config['driver'];
        if ($driver == 'sqlite') {
            return $this->loadforSQLite($config);
        }
        if ($driver == 'mysql') {
            return $config;
        }
        else {
            $errorMessage = "Only SQLite and MySQL database are supported at the moment.";
            throw new WrongDatabaseDriverException($errorMessage);
        }
    }

    public function loadforSQLite($config)
    {
        dd("ihihihih");
        $config['database'] = __DIR__ . '/../../../' . $config['database'];
        return $config;
    }

}
