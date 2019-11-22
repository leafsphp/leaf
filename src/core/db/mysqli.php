<?php
    namespace Leaf\Core\Db;

    class Mysqli {
        protected $connection;
        protected $queryResult;

        public function connect($host, $user, $password, $dbname) {
            try {
                $connection = mysqli_connect($host, $user, $password, $dbname);
                $this->connection = $connection;
                return $connection;
            } catch (\Exception $e) {
                echo $e;
            }
		}
		
        public function query(string $sql, $params = [], string $types = ''): self {
			if ($this->connection == null) {
				echo "Initialise your database first with connect()";
			}

			if(!is_array($params)) $params = [$params];

			if(!$types) $types = str_repeat('s', count($params));

			if(!$params) {
				$this->queryResult = $this->connection->query($sql);
			} else {
				$stmt = $this->stmt = $this->connection->prepare($sql);
				$stmt->bind_param($types, ...$params);
				$stmt->execute();
				$this->queryResult = $stmt->get_result();
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

		public function insert(string $table, string $column, string $value, array $params = []) {
			$this->query("INSERT INTO $table ($column) VALUES ($value)", $params);
			
			return $this;
		}

		public function count() {
			return mysqli_num_rows($this->queryResult);
		}

        public function fetchAssoc() {
            return mysqli_fetch_assoc($this->queryResult);
		}
		
		public function fetchObj() {
			return mysqli_fetch_object($this->queryResult);
        }

        public function fetchAll($type = MYSQLI_ASSOC) {
            return mysqli_fetch_all($this->queryResult, $type);
        }

        public function result() {
            return $this->queryResult;
        }
    }
