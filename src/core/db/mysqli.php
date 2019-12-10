<?php
    namespace Leaf\Core\Db;

	/**
	 * Leaf Core MYSQLI
	 * -----------------------
	 * Leaf's adaptation of **mysqli**
	 */
    class Mysqli {
        protected $connection;
        protected $queryResult;

		/**
		 * Connect to database
		 * 
		 * @param string $host: Host Name
		 * @param string $user: Database username
		 * @param string $password: Database password
		 * @param string $dbname: Database name
		 */
        public function connect($host, $user, $password, $dbname) {
            try {
                $connection = mysqli_connect($host, $user, $password, $dbname);
                $this->connection = $connection;
                return $connection;
            } catch (\Exception $e) {
                echo $e;
            }
		}
		
		/**
		 * MYSQLI Query
		 * 
		 * @param string $sql: Query
		 * @param array $params: prepared statement params if any
		 * @param string $types: Database password
		 */
        public function query(string $sql, array $params = [], string $types = ''): self {
			if ($this->connection == null) {
				echo "Initialise your database first with connect()";
				exit();
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
			return mysqli_num_rows($this->queryResult);
		}

        public function fetchAssoc() {
            return mysqli_fetch_assoc($this->queryResult);
		}
		
		public function fetchObj() {
			return mysqli_fetch_object($this->queryResult);
        }

        public function fetchAll($type = MYSQLI_ASSOC) {
			if ($type == "num") {
				$type = MYSQLI_NUM;
			}
			if ($type != "num" && $type != MYSQLI_NUM || $type == "assoc") {
				$type = MYSQLI_ASSOC;
			}
            return mysqli_fetch_all($this->queryResult, $type);
		}

		/**
		 * Get number of rows from SELECT
		 *
		 * @return int $connection->num_rows
		 */
		public function numRows(): int {
			return $this->connection->num_rows;
		}
		
		/**
		 * Get affected rows. Can be used instead of numRows() in SELECT
		 *
		 * @return int $connection->affected_rows or rows matched if setRowsMatched() is used
		 */
		public function affectedRows(): int {
			return $this->connection->affected_rows;
		}

		/**
		 * A more specific version of affectedRows() to give you more info what happened. Uses $connection::info under the hood
		 * Can be used for the following cases http://php.net/manual/en/mysqli.info.php
		 *
		 * @return array Associative array converted from result string
		 */
		public function info(): array {
			preg_match_all('/(\S[^:]+): (\d+)/', $this->connection->info, $matches);
			return array_combine($matches[1], $matches[2]);
		}

		/**
		 * Get rows matched instead of rows changed. Can strictly be used on UPDATE. Otherwise returns false
		 *
		 * @return int Rows matched
		 */
		public function rowsMatched(): int {
			return $this->info()['Rows matched'] ?? false;
		}

		/**
		 * Get the latest primary key inserted
		 *
		 * @return int $connection->insert_id
		 */
		public function insertId(): int {
			return $this->connection->insert_id;
		}

        public function result() {
            return $this->queryResult;
		}

		public function get() {
            return $this->queryResult;
		}
		
		/**
		 * Closes MySQL connection
		 *
		 */
		public function close(): void {
			$this->connection->close();
		}
    }
