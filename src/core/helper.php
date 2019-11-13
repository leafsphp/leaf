<?php
    namespace Leaf\Core;

    use Leaf\Helpers\JWT;

    class BaseHelper extends Controller {
        public function __construct() {
            parent::__construct();
        }

        public function jwt() {
            return new JWT;
        }
    }
