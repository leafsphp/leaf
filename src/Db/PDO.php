<?php

namespace Leaf\Db;

use \Leaf\Form;
use \Leaf\Http\Response;

/**
 * Leaf PDO
 * -----------------------
 * ***deprecation warning - use with care*** Leaf's adaptation of **PDO**
 */
class PDO
{
	protected $connection;
	protected $queryResult;

	public function __construct($host = null, $user = null, $password = null, $dbname = null, $db_type = "mysql")
	{
		$this->form = new Form;
		$this->response = new Response;

		if ($host != null || $user != null || $password != null || $dbname != null) {
			return $this->connect($host, $dbname, $user, $password, $db_type);
		}
		return;
	}

	/* 
	* Connect to database
	* 
	* @param string $host: Host Name
	* @param string $dbname: Database name
	* @param string $user: Database username
	* @param string $password: Database password
	*/
	public function connect($host, $dbname, $user, $password, $db_type = "mysql")
	{
		try {
			$connection = new \PDO("$db_type:host=$host;dbname=$dbname", $user, $password);
			$connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
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
			getenv("DB_DATABASE"),
			getenv("DB_USERNAME"),
			getenv("DB_PASSWORD"),
			getenv("DB_CONNECTION")
		);
	}

	/**
	 * Db Query
	 * 
	 * @param string $query: Query
	 * @param array $params: prepared statement params if any
	 */
	public function query(string $query, array $params = [])
	{
		if ($this->connection == null) {
			echo "Initialise your database first with connect()";
			exit();
		}

		if (!$params) {
			$this->queryResult = $this->connection->query($query);
		} else {
			$stmt = $this->connection->prepare($query);
			$this->queryResult = $stmt->execute($params);
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
	public function choose(string $table, string $items = "*", array $condition = [], string $options = null, $default_checks = true, $validate = [])
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
	public function add(string $table, array $items, array $uniques, $default_checks = true, array $validate = [])
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
			return $this;
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

	public function count()
	{
		return \count($this->fetchAll());
	}

	/**
	 * Fetch Query Results as object
	 */
	public function fetchObj()
	{
		return $this->queryResult->fetch(\PDO::FETCH_OBJ);
	}

	/**
	 * Fetch Query Results as assoc array
	 */
	public function fetchAssoc()
	{
		return $this->queryResult->fetch(\PDO::FETCH_ASSOC);
	}

	public function fetchAll($type = "assoc")
	{
		if ($type == "obj" || $type == "object") {
			return $this->queryResult->fetchAll(\PDO::FETCH_OBJ);
		} else {
			return $this->queryResult->fetchAll(\PDO::FETCH_ASSOC);
		}
	}

	public function result()
	{
		return $this->queryResult;
	}

	public function close()
	{
		echo "so";
	}
}
