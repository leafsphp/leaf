<?php
    namespace Leaf\Core\Db;

    class PDO {
        protected $connection;
        protected $queryResult;

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

        public function query(string $query, array $params = []) {
            if ($this->connection == null) {
                echo "Initialise your database first with connectMysqli()";
            }
            
            if (count($params) != 0) {
                $stmt = $this->connection->prepare($query);
                $stmt->bind_param(1);
                // foreach ($params as $param) {
				// }
				mysqli_fetch_all($array, MYSQLI_ASSOC);

                $this->queryResult = $stmt->execute();
            } else {
                $this->queryResult = mysqli_query($this->connection, $query);
            }

            return $this;
        }

        public function mysqliFetchAssoc() {
            return mysqli_fetch_assoc($this->queryResult);
        }

        public function mysqliFetchAll() {
            return mysqli_fetch_all($this->queryResult);
        }

        public function result() {
            return $this->queryResult;
        }
    }
