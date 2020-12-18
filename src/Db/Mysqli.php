<?php

namespace Leaf\Db;

use \Leaf\Form;
use \Leaf\Http\Response;

/**
 * Leaf MYSQLI
 * -----------------------
 * Leaf's adaptation of `mysqli`
 * **Deprecation Warning** This package will no longer be supported in later major releases.
 */
class Mysqli
{
	protected $connection;
	protected $queryResult;
	protected $errorsArray;

	public function __construct($host = null, $user = null, $password = null, $dbname = null)
	{
		$this->form = new Form;
		$this->response = new Response;

		if ($host != null || $user != null || $password != null || $dbname != null) {
			return $this->connect($host, $user, $password, $dbname);
		}
		return;
	}

	/**
	 * Connect to database
	 * 
	 * @param string $host: Host Name
	 * @param string $user: Database username
	 * @param string $password: Database password
	 * @param string $dbname: Database name
	 */
	public function connect($host, $user, $password, $dbname)
	{
		try {
			$connection = mysqli_connect($host, $user, $password, $dbname);
			$this->connection = $connection;
			return $connection;
		} catch (\Exception $e) {
			echo $e;
		}
	}

	/**
	 * Connect to database using environment variables
	 */
	public function auto_connect()
	{
		$this->connect(
			getenv("DB_HOST"),
			getenv("DB_USERNAME"),
			getenv("DB_PASSWORD"),
			getenv("DB_DATABASE")
		);
	}

	/**
	 * MYSQLI Query
	 * 
	 * @param string $sql: Query
	 * @param array $params: prepared statement params if any
	 * @param string $types: Database password
	 */
	public function query(string $sql, array $params = [], string $types = ''): self
	{
		if ($this->connection == null) {
			echo "Initialise your database first with connect()";
			exit();
		}

		if (!$types) $types = str_repeat('s', count($params));

		if (!$params) {
			$this->queryResult = $this->connection->query($sql);
		} else {
			$stmt = $this->stmt = $this->connection->prepare($sql);
			$stmt->bind_param($types, ...$params);
			$stmt->execute();
			$this->queryResult = $stmt->get_result();
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
	public function select(string $table, string $items = "*", string $options = "", array $params = [])
	{
		if (strlen($options) > 1) {
			$this->query("SELECT $items FROM $table WHERE $options", $params);
		} else {
			$this->query("SELECT $items FROM $table", $params);
		}

		return $this;
	}

	/**
	 * DB Choose
	 * 
	 * A simpler, more concise syntax for db->select. Uses prepared statements by default.
	 * 
	 * @param string table: Table to select from
	 * 
	 * @return array
	 */
	public function choose(string $table, string $items = "*", array $condition = [], string $options = null, $default_checks = false, $validate = [])
	{
		$data = [];
		if (count($condition) > 0) {
			$keys = [];

			foreach ($condition as $key => $value) {
				// try {
				// 	!$this->select($table, "*", "$key = ?", [$value]);
				// } catch (\Throwable $th) {
				// 	$this->response->throwErr(["error" => "$key is not a valid column in the $table table"]);
				// 	exit();
				// }

				array_push($keys, $key);
				array_push($data, $value);

				if ($default_checks == true) {
					if ($key == "email") $this->form->validate(["email" => "email"]);
					else if ($key == "username") $this->form->validate(["username" => "validusername"]);
					else $this->form->validate([$key => "required"]);
				}

				if (count($validate) > 0) {
					$this->form->validate($validate);
				}
			}

			$keys_length = count($keys);
			$data_length = count($data);
		}

		if (!empty($this->form->errors())) {
			foreach ($this->form->errors() as $key => $value) {
				$this->errorsArray[$key] = $value;
			}
			return $this;
		} else {
			$query = "";

			if (count($condition) > 0) {
				for ($i = 0; $i < $keys_length; $i++) {
					$query = $query . $keys[$i] . " = ?";
					if ($i < $keys_length - 1) {
						$query = $query . " AND ";
					}
				}
			}

			$query = $options == null ? $query : "$query $options";

			$this->select($table, $items, $query, $data);

			return $this;
		}
	}

	/**
	 * DB Add
	 * 
	 * A simpler, more concise syntax for db->insert. Uses prepared statements by default.
	 * 
	 * @param string table: Table to select from
	 * 
	 * @return array
	 */
	public function add(string $table, array $items, array $uniques = null, $default_checks = false, array $validate = [])
	{
		$data = [];
		$keys = [];

		foreach ($items as $key => $value) {
			// try {
			// 	!$this->select($table, "*", "$key = ?", [$value]);
			// } catch (\Throwable $th) {
			// 	$this->response->throwErr(["error" => "$key is not a valid column in the $table table"]);
			// 	exit();
			// }

			array_push($keys, $key);
			array_push($data, $value);

			if ($default_checks == true) {
				if ($key == "email") $this->form->validate(["email" => "email"]);
				else if ($key == "username") $this->form->validate(["username" => "validusername"]);
				else $this->form->validate([$key => "required"]);
			}

			if (count($validate) > 0) {
				$this->form->validate($validate);
			}
		}

		$keys_length = count($keys);
		$data_length = count($data);

		if ($uniques != null) {
			foreach ($uniques as $unique) {
				if (!isset($items[$unique])) {
					$this->response->respond(["error" => "$unique not found, Add $unique to your \$db->add items or check your spelling."]);
					exit();
				} else {
					if ($this->select($table, "*", "$unique = ?", [$items[$unique]])->fetchObj()) {
						$this->form->errorsArray[$unique] = "$unique already exists";
					}
				}
			}
		}

		if (!empty($this->form->errors())) {
			foreach ($this->form->errors() as $key => $value) {
				$this->errorsArray[$key] = $value;
			}
			return false;
		} else {
			$table_names = "";
			$table_values = "";

			for ($i = 0; $i < $keys_length; $i++) {
				$table_names = $table_names . $keys[$i];
				if ($i < $keys_length - 1) {
					$table_names = $table_names . ", ";
				}

				$table_values = $table_values . "?";
				if ($i < $keys_length - 1) {
					$table_values = $table_values . ", ";
				}
			}

			$this->insert($table, $table_names, $table_values, $data);
		}
	}

	/**
	 * Db SelectFew
	 * 
	 * retrieve a limited number of rows from table
	 * 
	 * @param string $table: Db Table
	 * @param string $items: Specific table columns to fetch
	 * @param string $options: Condition to fetch on
	 * @param array $params: prepared statement params if any
	 */
	public function selectFew($limit, string $table, string $items = "*", string $options = "", array $params = [])
	{
		if (strlen($options) > 1) {
			$this->query("SELECT $items FROM $table WHERE $options $limit", $params);
		} else {
			$this->query("SELECT $items FROM $table $limit", $params);
		}

		return $this;
	}

	public function delete(string $table, string $options = "", array $params = [])
	{
		if (strlen($options) > 1) {
			$this->query("DELETE FROM $table WHERE $options", $params);
		} else {
			$this->query("DELETE FROM $table", $params);
		}

		return $this;
	}

	public function insert(string $table, string $column, string $value, array $params = [])
	{
		$this->query("INSERT INTO $table ($column) VALUES ($value)", $params);

		return $this;
	}

	public function update(string $table, string $updateOptions, string $options, array $params = [])
	{
		if (strlen($options) > 1) {
			$this->query("UPDATE $table SET $updateOptions WHERE $options", $params);
		} else {
			$this->query("UPDATE $table SET $updateOptions", $params);
		}

		return $this;
	}

	/**
	 * Get number of rows from SELECT
	 *
	 * @return int $connection->num_rows
	 */
	public function count()
	{
		return mysqli_num_rows($this->queryResult);
	}

	public function fetchAssoc()
	{
		return mysqli_fetch_assoc($this->queryResult);
	}

	public function fetchObj()
	{
		return mysqli_fetch_object($this->queryResult);
	}

	public function fetchAll($type = MYSQLI_ASSOC)
	{
		if ($type == "num" || $type == "obj") {
			$type = MYSQLI_NUM;
		} else {
			$type = MYSQLI_ASSOC;
		}
		return mysqli_fetch_all($this->queryResult, $type);
	}

	/**
	 * Get affected rows. Can be used instead of numRows() in SELECT
	 *
	 * @return int $connection->affected_rows or rows matched if setRowsMatched() is used
	 */
	public function affectedRows(): int
	{
		return $this->connection->affected_rows;
	}

	/**
	 * A more specific version of affectedRows() to give you more info what happened. Uses $connection::info under the hood
	 * Can be used for the following cases http://php.net/manual/en/mysqli.info.php
	 *
	 * @return array Associative array converted from result string
	 */
	public function info(): array
	{
		preg_match_all('/(\S[^:]+): (\d+)/', $this->connection->info, $matches);
		return array_combine($matches[1], $matches[2]);
	}

	/**
	 * Get rows matched instead of rows changed. Can strictly be used on UPDATE. Otherwise returns false
	 *
	 * @return int Rows matched
	 */
	public function rowsMatched(): int
	{
		return $this->info()['Rows matched'] ?? false;
	}

	/**
	 * Get the latest primary key inserted
	 *
	 * @return int $connection->insert_id
	 */
	public function insertId(): int
	{
		return $this->connection->insert_id;
	}

	public function result()
	{
		return $this->queryResult;
	}

	public function fetch()
	{
		return $this->queryResult;
	}

	/**
	 * Closes MySQL connection
	 */
	public function close(): void
	{
		$this->connection->close();
	}

	public function errors()
	{
		return $this->errorsArray;
	}

	// todo

	// /**
	//  * DB Edit
	//  * -------------------
	//  * A simpler, more concise syntax for `db->update`. Uses prepared statements by default.
	//  * 
	//  * @param string table: Table to select from
	//  * 
	//  * @return array
	//  */
	// public function edit(string $table, array $items, array $uniques = null, $default_checks = false, array $validate = [])
	// {
	// }
}
