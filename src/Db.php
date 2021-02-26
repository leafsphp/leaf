<?php

namespace Leaf;

/**
 * Leaf DB
 * ------------------
 * Simple query-builder powered by Mysqli. This is supposed to replace the single DB\Mysqli & DB\PDO packages 
 * 
 * @author Michael Darko
 * @since v2.1.0
 */
class Db
{
	/**
	 * Database Connection
	 */
	protected $connection;

	/**
	 * Raw query with query options
	 */
	protected $queryData = [
		"table" => "",
		"type" => "",
		"query" => "",
		"bindings" => [],
		"uniques" => [],
		"validate" => [],
		"values" => [],
		"hidden" => [],
		"add" => []
	];

	/**
	 * Query identifiers
	 */
	protected $identifiers = [
		"insert" => "INSERT INTO ",
		"select" => "SELECT ",
		"update" => "UPDATE ",
		"delete" => "DELETE FROM "
	];

	/**
	 * Query result
	 */
	protected $queryResult;

	/**
	 * Any errors caught
	 */
	protected $errorsArray = [];

	/**
	 * Leaf Form Module
	 */
	protected $form;

	/**
	 * List of methods called
	 */
	protected $callStack = [];

	public function __construct($host = null, $user = null, $password = null, $dbname = null)
	{
		$this->form = new Form;

		if ($host != null || $user != null || $password != null || $dbname != null) {
			$this->connect($host, $user, $password, $dbname);
		}
	}

	/**
	 * Return the database connection
	 */
	public function connection()
	{
		return $this->connection;
	}

	/**
	 * Connect to database
	 * 
	 * @param string $host: Host Name
	 * @param string $user: Database username
	 * @param string $password: Database password
	 * @param string $dbname: Database name
	 */
	public function connect(string $host, string $user, string $password, string $dbname): void
	{
		try {
			$connection = mysqli_connect($host, $user, $password, $dbname);
			$this->connection = $connection;
		} catch (\Exception $e) {
			$this->connection = null;
			$this->errorsArray["connection"] = $e->getMessage();
		}

		$this->callStack[] = "connect";
	}

	/**
	 * Connect to database using environment variables
	 */
	public function autoConnect(): void
	{
		$this->connect(
			getenv("DB_HOST"),
			getenv("DB_USERNAME"),
			getenv("DB_PASSWORD"),
			getenv("DB_DATABASE")
		);

		$this->callStack[] = "autoConnect";
	}

	/**
	 * Manually create a database query
	 * 
	 * @param string $sql Full db query
	 */
	public function query(string $sql): self
	{
		$this->queryData["query"] = $sql;

		foreach ($this->identifiers as $key => $value) {
			if (strpos(strtoupper($sql), $value) === 0) {
				$this->queryData["type"] = $key;
				break;
			}
		}

		if ($this->queryData["table"] === "") {
			$data = explode(" ", $sql);

			if ($data[0] === "SELECT" || $data[0] === "UPDATE") {
				$this->queryData["table"] = $data[1];
			} else {
				$this->queryData["table"] = $data[2];
			}
		}

		$this->callStack[] = "query";

		return $this;
	}

	/**
	 * Db Select
	 * 
	 * Retrieve a row from table
	 * 
	 * @param string $table: Db Table
	 * @param string $items: Specific table columns to fetch
	 */
	public function select(string $table, string $items = "*")
	{
		$this->query("SELECT $items FROM $table");
		$this->queryData["table"] = $table;
		$this->callStack[] = "select";

		return $this;
	}

	/**
	 * Db Insert
	 * 
	 * Add a new row in a db table
	 * 
	 * @param string $table: Db Table
	 */
	public function insert(string $table): self
	{
		$this->query("INSERT INTO $table");
		$this->queryData["table"] = $table;
		$this->callStack[] = "insert";

		return $this;
	}

	/**
	 * Db Update
	 * 
	 * Update a row in a db table
	 * 
	 * @param string $table: Db Table
	 */
	public function update(string $table): self
	{
		$this->query("UPDATE $table");
		$this->queryData["table"] = $table;
		$this->callStack[] = "update";

		return $this;
	}

	/**
	 * Db Delete
	 * 
	 * Delete a table's records
	 * 
	 * @param string $table: Db Table
	 */
	public function delete(string $table): self
	{
		$this->query("DELETE FROM $table");
		$this->queryData["table"] = $table;
		$this->callStack[] = "delete";

		return $this;
	}

	/**
	 * Pass in parameters into your query
	 * 
	 * @param array $params Params to pass into query
	 */
	public function params(array $params): self
	{
		$query = $this->queryData["type"] == "update" ? " SET " : " ";

		$count = 0;
		$dataToBind = [];
		$keys = "";
		$values = "";

		foreach ($params as $key => $value) {
			if ($this->queryData["type"] == "insert") {
				$keys .= $key;
				$values .= "?";
				if ($count < count($params) - 1) {
					$keys .= ", ";
					$values .= ", ";
				}
			} else if ($this->queryData["type"] == "update") {
				$query .= "$key = ?";
				if ($count < count($params) - 1) {
					$query .= ", ";
				}
			}
			$dataToBind[] = $value;
			$count += 1;
		}

		if ($this->queryData["type"] == "insert") {
			$query .= "($keys) VALUES ($values)";
		}

		$this->bind($dataToBind);
		$this->queryData["query"] .= $query;
		$this->queryData["values"] = $params;

		$this->callStack[] = "params";

		return $this;
	}

	/**
	 * Controls inner workings of all where blocks
	 */
	protected function baseWhere($condition, $value = null, $comparator = "=", $operation = "AND")
	{
		$query = "";

		if (!in_array("where", $this->callStack)) {
			$query = " WHERE ";
		}

		$count = 0;
		$dataToBind = [];
		$params = [];
		$comparator ?? "=";

		if (is_array($condition)) {
			foreach ($condition as $key => $value) {
				$query .= "$key $comparator ?";
				if ($count < count($condition) - 1) {
					$query .= " $operation ";
				}
				if ($this->queryData["type"] === "select" || $this->queryData["type"] === "delete") {
					$params[$key] = $value;
				}
				$dataToBind[] = $value;
				$count += 1;
			}
		} else {
			if (!$value) {
				$query .= $condition;
			} else {
				if ($this->queryData["type"] === "select" || $this->queryData["type"] === "delete") {
					$params[$condition] = $value;
				}
				$query .= "$condition $comparator ?";
				$dataToBind[] = $value;
			}
		}

		$this->bind($dataToBind);

		if ($this->queryData["type"] === "select" || $this->queryData["type"] === "delete") {
			$this->queryData["values"] = $params;
		}

		$this->queryData["query"] .= $query;
		$this->callStack[] = "where";

		return $this;
	}

	/**
	 * Add a where clause to db query
	 * 
	 * @param string|array $condition
	 * @param string|null $value
	 */
	public function where($condition, $value = null): self
	{
		return $this->baseWhere($condition, $value);
	}

	/**
	 * Controls inner workings of orWhere
	 */
	protected function baseOrWhere($condition, $value = null, $operation = "=")
	{
		if (in_array("where", $this->callStack)) {
			$this->queryData["query"] .= " OR ";
		}

		$this->callStack[] = "orWhere";
		return $this->baseWhere($condition, $value, $operation, "OR");
	}

	/**
	 * Add a where clause with OR comparator to db query
	 * 
	 * @param string|array $condition
	 * @param string|null $value
	 */
	public function orWhere($condition, $value = null): self
	{
		return $this->baseOrWhere($condition, $value);
	}

	/**
	 * Add a where clause with LIKE comparator to db query
	 * 
	 * @param string|array $condition
	 * @param string|null $value
	 */
	public function whereLike($condition, $value = null): self
	{
		$this->callStack[] = "whereLike";
		return $this->baseWhere($condition, $value, "LIKE");
	}

	/**
	 * Add a where clause with LIKE comparator to db query
	 * 
	 * @param string|array $condition
	 * @param string|null $value
	 */
	public function orWhereLike($condition, $value = null): self
	{
		$this->callStack[] = "orWhereLike";
		return $this->orWhere($condition, $value, "LIKE");
	}

	/**
	 * Alias for `whereLike`
	 * 
	 * @param string|array $condition
	 * @param string|null $value
	 */
	public function like($condition, $value = null): self
	{
		$this->callStack[] = "like";
		return $this->whereLike($condition, $value);
	}

	/**
	 * Alias for `orWhereLike`
	 * 
	 * @param string|array $condition
	 * @param string|null $value
	 */
	public function orLike($condition, $value = null): self
	{
		$this->callStack[] = "orLike";
		return $this->orWhereLike($condition, $value);
	}

	/**
	 * Set a max number of resources
	 * 
	 * @param mixed $limit The number of rows to fetch
	 */
	public function limit($limit): self
	{
		$this->queryData["query"] .= " LIMIT $limit";

		$this->callStack[] = "limit";
		return $this;
	}

	/**
	 * Order results according to key
	 * 
	 * @param string $key The key to order results by
	 * @param string $direction The direction to order [DESC, ASC]
	 */
	public function orderBy($key, $direction = "desc"): self
	{
		$direction = strtoupper($direction);
		$this->queryData["query"] .= " ORDER BY $key $direction";

		$this->callStack[] = "orderBy";
		return $this;
	}

	/**
	 * Validate data before running a query
	 * 
	 * @param array|string $item The item(s) to validate
	 * @param string|null $rule The validation rule to apply
	 */
	public function validate($item, $rule = "required"): self
	{
		$values = $this->queryData["values"];

		if (is_array($item)) {
			foreach ($item as $key => $value) {
				$this->queryData["validate"][] = [$key, $values[$key], strtolower($value) ?? "required"];
			}
		} else {
			$this->queryData["validate"][] = [$item, $values[$item], strtolower($rule)];
		}
		return $this;
	}

	/**
	 * Make sure a value doesn't already exist in a table to avoid duplicates.
	 * 
	 * @param mixed $uniques Items to check for
	 */
	public function unique(...$uniques)
	{
		$data = [];
		foreach ($uniques as $unique) {
			if (is_array($unique)) {
				$data = $unique;
			} else {
				$data[] = $unique;
			}
		}
		$this->queryData["uniques"] = $data;

		return $this;
	}

	/**
	 * Hide particular fields from the final value returned
	 * 
	 * @param mixed $values The value(s) to hide
	 */
	public function hidden(...$values): self
	{
		$data = [];
		foreach ($values as $value) {
			if (is_array($value)) {
				$data = $value;
			} else {
				$data[] = $value;
			}
		}
		$this->queryData["hidden"] = $data;

		return $this;
	}

	/**
	 * Add particular fields to the final value returned
	 * 
	 * @param string|array $name What to add
	 * @param string $value The value to add
	 */
	public function add($name, $value = null): self
	{
		$data = [];
		if (is_array($name)) {
			$data = $name;
		} else {
			$data[$name] = $value;
		}
		$this->queryData["add"] = $data;

		return $this;
	}

	/**
	 * Bind parameters to a query
	 * 
	 * @param array|string $data The data to bind to string
	 */
	public function bind(...$bindings): self
	{
		$data = [];
		foreach ($bindings as $binding) {
			if (is_array($binding)) {
				$data = $binding;
			} else {
				$data[] = $binding;
			}
		}

		$this->queryData["bindings"] = array_merge($this->queryData["bindings"], $data);

		return $this;
	}

	/**
	 * Execute a query
	 * 
	 * @param array $paramTypes The types for parameters(defaults to strings)
	 * 
	 * @return null|void
	 */
	public function execute($paramTypes = null)
	{
		if ($this->connection === null) {
			trigger_error("Couldn't establish database connection. Call the connect() method, or check your database");
		}

		if (count($this->errorsArray) > 0) return null;

		$query = $this->queryData["query"];
		$bindings = $this->queryData["bindings"];
		$paramValues = $this->queryData["values"];
		$uniques = $this->queryData["uniques"];
		$validate = $this->queryData["validate"];

		if (count($validate) > 0) {
			foreach ($validate as $item) {
				if (!$this->form->validateField($item[0], $item[1], $item[2])) {
					foreach ($this->form->errors() as $name => $error) {
						$this->errorsArray[$name] = $error;
					}
				}
			}

			if (count($this->errorsArray) > 0) return null;
		}

		if (count($uniques) > 0 && ($this->queryData["type"] != "select" || $this->queryData["type"] != "delete")) {
			foreach ($uniques as $unique) {
				if (!isset($paramValues[$unique])) {
					trigger_error("$unique not found, Add $unique to your \$db->add items or check your spelling.");
				}

				if (mysqli_fetch_object($this->connection->query("SELECT * FROM {$this->queryData["table"]} WHERE $unique = '$paramValues[$unique]'"))) {
					$this->errorsArray[$unique] = "$unique already exists";
				}
			}

			if (count($this->errorsArray) > 0) return null;
		}

		if (!$bindings || count($bindings) === 0) {
			try {
				$this->queryResult = $this->connection->query($query);
			} catch (\Throwable $th) {
				$this->errorsArray["query"] = $th->getMessage();
			}
		} else {
			$stmt = $this->stmt = $this->connection->prepare($query);
			$stmt->bind_param($paramTypes ?? str_repeat('s', count($bindings)), ...$bindings);
			try {
				$stmt->execute();
			} catch (\Throwable $th) {
				$this->errorsArray["query"] = $th->getMessage();
			}
			$this->queryResult = $stmt->get_result();
		}

		if ($this->queryData["type"] !== "select") {
			$this->clearState();
		}
		$this->callStack = [];

		return true;
	}

	/**
	 * Get number of rows from SELECT
	 *
	 * @return int $connection->num_rows
	 */
	public function count(): int
	{
		if (!$this->execute()) return null;
		$this->clearState();

		return mysqli_num_rows($this->queryResult);
	}

	/**
	 * Fetch query results as an associative array
	 */
	public function fetchAssoc()
	{
		if (!$this->execute()) return null;
		$result = mysqli_fetch_assoc($this->queryResult);

		$add = $this->queryData["add"];
		if (count($add) > 0) {
			foreach ($add as $item => $value) {
				$result[$item] = $value;
			}
		}

		$hidden = $this->queryData["hidden"];

		if (count($hidden) > 0) {
			foreach ($hidden as $item) {
				if (isset($result[$item]) || $result[$item] === null) unset($result[$item]);
			}
		}

		$this->clearState();
		return $result;
	}

	/**
	 * Fetch query results as object
	 */
	public function fetchObj()
	{
		if (!$this->execute()) return null;
		$result = mysqli_fetch_object($this->queryResult);

		$add = $this->queryData["add"];
		if (count($add) > 0) {
			foreach ($add as $item => $value) {
				$result->{$item} = $value;
			}
		}

		$hidden = $this->queryData["hidden"];
		if (count($hidden) > 0) {
			foreach ($hidden as $item) {
				if (isset($result->{$item})) unset($result->{$item});
			}
		}

		$this->clearState();
		return $result;
	}

	/**
	 * Fetch all
	 */
	public function fetchAll()
	{
		if (!$this->execute()) return null;
		$result = mysqli_fetch_all($this->queryResult, \MYSQLI_ASSOC);

		$add = $this->queryData["add"];
		$hidden = $this->queryData["hidden"];
		$final = [];

		if (count($add) > 0 || count($hidden) > 0) {
			foreach ($result as $res) {
				if (count($add) > 0) {
					foreach ($add as $item => $value) {
						$res[$item] = $value;
					}
				}

				if (count($hidden) > 0) {
					foreach ($hidden as $item) {
						if (isset($res[$item])) unset($res[$item]);
					}
				}
				$final[] = $res;
			}
		} else {
			$final = $result;
		}

		$this->clearState();
		return $final;
	}

	/**
	 * Alias of fetchAll
	 */
	public function all()
	{
		return $this->fetchAll();
	}

	/**
	 * Get first matching result
	 */
	public function first()
	{
		$result = $this->fetchAll();
		return isset($result[0]) ? $result[0] : $result;
	}

	/**
	 * Get last matching result
	 */
	public function last()
	{
		$result = $this->fetchAll();
		return isset($result[count($result) - 1]) ? $result[count($result) - 1] : $result;
	}

	/**
	 * Return raw query result
	 */
	public function fetch(): array
	{
		if (!$this->execute()) return null;
		return $this->queryResult;
	}

	/**
	 * Set the current db table
	 */
	public function table($table)
	{
		$this->queryData["table"] = $table;

		return $this;
	}

	/**
	 * Search a db table for a value
	 */
	public function search($row, $value, $hidden = null)
	{
		return $this->select($this->queryData["table"])->like($row, static::includes($value))->hidden($hidden)->all();
	}

	/**
	 * Closes MySQL connection
	 */
	public function close(): void
	{
		$this->connection->close();
	}

	/**
	 * Return caught errors if any
	 */
	public function errors(): array
	{
		return $this->errorsArray;
	}

	protected function clearState()
	{
		$this->queryData = [
			"table" => "",
			"type" => "",
			"query" => "",
			"bindings" => [],
			"uniques" => [],
			"validate" => [],
			"values" => [],
			"hidden" => [],
			"add" => []
		];
		$this->callStack = [];
	}

	/**
	 * Construct search that begins with a phrase in db 
	 */
	public static function beginsWith($phrase)
	{
		return "$phrase%";
	}

	/**
	 * Construct search that ends with a phrase in db 
	 */
	public static function endsWith($phrase)
	{
		return "%$phrase";
	}

	/**
	 * Construct search that includes a phrase in db 
	 */
	public static function includes($phrase)
	{
		return "%$phrase%";
	}

	/**
	 * Construct search that begins and ends with a phrase in db 
	 */
	public static function word($beginsWith, $endsWith)
	{
		return "$beginsWith%$endsWith";
	}
}
