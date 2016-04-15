<?php

namespace Elchroy\Lemojis\Connections;

use Elchroy\Lemojis\Exceptions\WrongConfigurationException;
use Illuminate\Database\Capsule\Manager as Capsule;

class Connection
{
    /**
     * Public variable to hold the eloquent capsule.
     */
    public $capsule;

    /**
     * Contructor - Load the paths for the connection configurations details
     * Make the connection.
     *
     * @param [type] $path [description]
     */
    public function __construct($path = null)
    {
        $this->capsule = new Capsule();

        $configData = $this->loadConfiguration($path);

        $this->capsule->addConnection($configData);

        $this->capsule->bootEloquent();
        $this->capsule->setAsGlobal();

        // Hold a reference to established connection just in case.
        $this->connection = $this->capsule->getConnection('default');
    }

    /**
     * Load the configuration depending on the nature of the driver that is in the configuration file.
     * If the driver is not supported, then an exception is thrown.
     *
     * @param  [string] The path to the configuration file.
     *
     * @return [array] An array of the configuration details to be used for hte connection.
     */
    public function loadConfiguration($path = null)
    {
        $path = $path == null ? __DIR__.'/../../../../../../config.ini' : $path;
        $config = @parse_ini_file($path);
        if ($config == false ) {
            throw new WrongConfigurationException("Ensure that the config.ini file has been created at the root directory of your application.");
        }
        $driver = $config['driver'];
        if ($driver == 'sqlite') {
            return $this->loadforSQLite($config);
        }
        if ($driver == 'mysql') {
            return $this->loadforMySQL($config);
        } else {
            $errorMessage = 'Only SQLite and MySQL database are supported at the moment.';
            throw new WrongConfigurationException($errorMessage);
        }
    }

    /**
     * Load the connection if the driver is SQLite.
     *
     * @param  [array] The configurations details used for SQLite.
     *
     * @return [type] The configuration data with a updated with a link to the sqlite file.
     */
    public function loadforSQLite($config)
    {
        $config['database'] = __DIR__.'/../../../'.$config['database'];

        return $config;
    }

    /**
     * Load the connection for the mysql driver that is given in the database.
     *
     * @param [array] $config [description]
     *
     * @return [type] The configuration data with the connection information to be used for MySQL.
     */
    public function loadforMySQL($config)
    {
        return $config;
    }
}
