<?php
    namespace Leaf\Config;

    class Database {
        private $connection;

        public function __construct($host, $user, $password, $dbname, $connectionType = 'mysqli') {
            $connectionType = strtolower($connectionType);

            if ($connectionType == 'mysqli') {
                $this->connectMysqli($host, $user, $password, $dbname);
            } elseif  ($connectionType == 'pdo') {
                $this->connectPDO($host, $dbname, $user, $password);
            } else {
                echo "Connection type is not valid";
                exit(500);
            }
        }

        public function connectMysqli($host, $user, $password, $dbname) {
            try {
                $connection = mysqli_connect($host, $user, $password, $dbname);
                $this->connection = $connection;
                return $connection;
            } catch (\Exception $e) {
                echo $e;
            }
        }

        public function connectPDO($host, $dbname, $user, $password) {
            try {
                $connection = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
                $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->connection = $connection;
                return $connection;
            } catch (\Exception $e) {
                echo $e;
            }
        }

        public function mysqliQuery($query) {
            if ($this->connection == null) {
                echo "Initialise your database first with connectMysqli()";
            }
            $result = mysqli_query($this->connection, $query);
            return $result;
        }
    }
