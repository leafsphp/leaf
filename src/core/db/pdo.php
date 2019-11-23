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
            
            
            return $this;
		}
		
		public function select() {
			// 
		}

		public function update() {
			// 
		}

		public function delete() {
			// 
		}

		public function insert() {
			// 
		}

		public function count() {
			// 
		}

		public function fetchObj() {
			// 
		}

        public function fetchAssoc() {
            // 
        }

        public function fetchAll() {
            // 
        }

        public function result() {
            return $this->queryResult;
		}
		
		public function close() {
			// 
		}
    }
