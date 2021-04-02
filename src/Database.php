<?php

namespace Leaf;

use \Illuminate\Database\Capsule\Manager;
use \Illuminate\Events\Dispatcher;
use \Illuminate\Container\Container;

class Database
{
    public $capsule;

    public function __construct()
    {
        $this->capsule = new Manager;
        $this->capsule->addConnection([
            "driver" =>  getenv('DB_CONNECTION'),
            "host" =>  getenv('DB_HOST'),
            "database" =>  getenv('DB_DATABASE'),
            "username" =>  getenv('DB_USERNAME'),
            "password" =>  getenv('DB_PASSWORD'),
            // "timezone" =>  getenv('DB_TIMEZONE'),
            "charset" =>  getenv('DB_CHARSET') ? getenv('DB_CHARSET') : "utf8",
            "collation" =>  getenv('DB_COLLATION') ? getenv('DB_COLLATION') : "utf8_general_ci",
            "prefix" =>  getenv('DB_PREFIX') ? getenv('DB_PREFIX') : ""
        ]);

        $this->capsule->setEventDispatcher(new Dispatcher(new Container));
        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();
    }
}
