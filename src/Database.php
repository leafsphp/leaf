<?php

namespace Leaf;

use \Illuminate\Database\Capsule\Manager;
use \Illuminate\Events\Dispatcher;
use \Illuminate\Container\Container;

class Database
{
    public static $capsule;

    protected static $config = [];

    public static function config($config = [])
    {
        if (empty($config)) {
            return static::$config;
        }

        static::$config = array_merge(static::$config, $config);
    }

    public static function connect()
    {
        $connection = static::$config["default"] ?? "mysql";

        static::$capsule = new Manager;
        static::$capsule->addConnection(
            static::$config["connections"][$connection]
        );

        static::$capsule->setEventDispatcher(new Dispatcher(new Container));
        static::$capsule->setAsGlobal();
        static::$capsule->bootEloquent();
    }
}
