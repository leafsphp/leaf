<?php
namespace Leaf\Core;

use Leaf\Core\Database;
new Database();

class Model extends \Illuminate\Database\Eloquent\Model {
	public function __construct() {
        parent::__construct();
    }
}
