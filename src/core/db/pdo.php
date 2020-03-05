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
		
		public function __construct($host = null, $user = null, $password = null, $dbname = null) {
			if ($host != null || $user != null || $password != null || $dbname != null) {
				return $this->connect($host, $dbname, $user, $password);
			}
			return;
		}

		/* Connect to database
		 * 
		 * @param string $host: Host Name
		 * @param string $dbname: Database name
		 * @param string $user: Database username
		 * @param string $password: Database password
		 */
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

		/**
		 * Db Query
		 * 
		 * @param string $query: Query
		 * @param array $params: prepared statement params if any
		 */
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
		
		/**
		 * Db Select
		 * 
		 * retrieve a row from table
		 * 
		 * @param string $table: Db Table
		 * @param string $items: Specific table columns to fetch
		 * @param string $options: Condition to fetch on
		 * @param array $params: prepared statement params if any
		 */
		public function select(string $table, string $items = "*", string $options = "", array $params = []) {
			if (strlen($options) > 1) {
				$this->query("SELECT $items FROM $table WHERE $options", $params);
			} else {
				$this->query("SELECT $items FROM $table", $params);
			}
			
			return $this;
		}

		/**
		 * Db Choose
		 * 
		 * retrieve a limited number of rows from table
		 * 
		 * @param string $table: Db Table
		 * @param string $items: Specific table columns to fetch
		 * @param string $options: Condition to fetch on
		 * @param array $params: prepared statement params if any
		 */
		public function choose($limit, string $table, string $items = "*", string $options = "", array $params = []) {
			if (strlen($options) > 1) {
				$this->query("SELECT $items FROM $table WHERE $options LIMIT $limit", $params);
			} else {
				$this->query("SELECT $items FROM $table LIMIT $limit", $params);
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
