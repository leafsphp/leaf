<?php

namespace Leaf;

new \Leaf\Database();

class Model extends \Illuminate\Database\Eloquent\Model
{
    public function __construct()
    {
        parent::__construct();
    }
}
