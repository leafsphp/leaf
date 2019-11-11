<?php
    namespace Leaf\Config;

    class Database {
        public function connectMysqli($host, $user, $password, $dbname) {
            try {
                return mysqli_connect($host, $user, $password, $dbname);
            } catch (\Exception $e) {
                echo $e;
            }
        }

        public function connectPDO($host, $dbname, $user, $password) {
            try {
                $connection = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
                $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return $connection;
            } catch (\Exception $e) {
                echo $e;
            }
        }
    }
