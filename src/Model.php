<?php

namespace Leaf;

Database::connect();

class Model extends \Illuminate\Database\Eloquent\Model
{
    public function __construct()
    {
        parent::__construct();
    }
}
