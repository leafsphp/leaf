<?php
    namespace Leaf\Core\Db;

    /**
	 * Leaf Core PDO
	 * -----------------------
	 * Leaf's adaptation of **PDO**
	 */
    class PDO {
        protected $connection;
        protected $queryResult;

        public function connect($host, $dbname, $user, $password) {
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
                echo "Initialise your database first with connect()";
                exit();
            }
            
            if(!$params) {
                $this->queryResult = $this->connection->query($query);
            } else {
                $stmt = $this->connection->prepare($query);
                $stmt->bindParam(...$params);
                $this->queryResult = $stmt->execute();
            }
            
            return $this;
		}
		
		public function select(string $table, string $items = "*", string $options = "", array $params = []) {
			if (strlen($options) > 1) {
				$this->query("SELECT $items FROM $table WHERE $options", $params);
			} else {
				$this->query("SELECT $items FROM $table", $params);
			}
			
			return $this;
		}

		public function delete(string $table, string $options = "", array $params = []) {
			if (strlen($options) > 1) {
				$this->query("DELETE FROM $table WHERE $options", $params);
			} else {
				$this->query("DELETE FROM $table", $params);
			}
			
			return $this;
		}

		public function insert(string $table, string $column, string $value, array $params = []) {
			$this->query("INSERT INTO $table ($column) VALUES ($value)", $params);
			
			return $this;
		}

		public function update(string $table, string $updateOptions, string $options, array $params = []) {
			if (strlen($options) > 1) {
				$this->query("UPDATE $table SET $updateOptions WHERE $options", $params);
			} else {
				$this->query("UPDATE $table SET $updateOptions", $params);
			}
			
			return $this;
		}

		public function count() {
			// 
		}

		public function fetchObj() {
			return $this->queryResult->fetch(PDO::FETCH_OBJ);
		}

        public function fetchAssoc() {
            return $this->queryResult->fetch(PDO::FETCH_ASSOC);
        }

        public function fetchAll($type = FETCH_OBJ) {
            if ($type == "obj" || $type == "object" || $type == FETCH_OBJ) {
				$type = FETCH_OBJ;
			}
			if ($type != "obj" && $type != FETCH_OBJ || $type == "assoc") {
				$type = FETCH_ASSOC;
            }
            $this->queryResult->fetchAll(PDO::$type);
        }

        public function result() {
            return $this->queryResult;
		}
		
		public function close() {
			// 
		}
    }
