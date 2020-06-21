<?php
namespace Leaf;

/**
 * Leaf DB
 * ------------------
 * Simple query-builder powered by Mysqli
 * 
 * @author Michael Darko
 * @since v2.1.0
 */
class Db {
	/**
	 * Database Connection
	 */
	protected $connection;
	/**
	 * Raw query with any param bindings
	 */
	protected $queryData = [
		"type" => "",
		"query" => "",
		"params" => [],
		"uniques" => [],
		"values" => []
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
	 * Leaf Response Module
	 */
	protected $response;

	public function __construct($host = null, $user = null, $password = null, $dbname = null)
	{
		$this->form = new Form;
		$this->response = new Http\Response;

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
	public function connect(string $host, string $user, string $password, string $dbname) : void
	{
		try {
			$connection = mysqli_connect($host, $user, $password, $dbname);
			$this->connection = $connection;
		} catch (\Exception $e) {
			$this->connection = null;
			$this->errorsArray["connection"] = $e->getMessage();
		}
	}

	/**
	 * Connect to database using environment variables
	 */
	public function auto_connect() : void
	{
		$this->connect(
			getenv("DB_HOST"),
			getenv("DB_USERNAME"),
			getenv("DB_PASSWORD"),
			getenv("DB_DATABASE")
		);
	}

	/**
	 * DB Query
	 * ----------------
	 * Create a database query
	 * 
	 * @param string $sql: Query
	 */
	public function query(string $sql): self
	{
		if ($this->connection == null) $this->response->throwErr("Initialise your database first with connect()");
		$this->queryData["type"] = "query";
		$this->queryData["query"] = $sql;

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
		$this->queryData["query"] .= "SELECT $items FROM $table";
		$this->queryData["type"] = "select";
		return $this;
	}

	/**
	 * Db Insert
	 * 
	 * Retrieve a row from table
	 * 
	 * @param string $table: Db Table
	 */
	public function insert(string $table) : self
	{
		$this->queryData["query"] .= "INSERT INTO $table";
		$this->queryData["type"] = "insert";
		return $this;
	}

	/**
	 * Pass in parameters into your query
	 * 
	 * @param array $params Params to pass into query
	 */
	public function params(array $params) : self
	{
		if ($this->queryData["type"] == "query") {
			if (strpos($this->queryData["query"], "INSERT INTO") === 0) $this->queryData["type"] = "insert";
			if (strpos($this->queryData["query"], "UPDATE") === 0) $this->queryData["type"] = "update";
		}
		$query = " ";
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
			$dataToBind[$value] = "s";
			$count += 1;
		}
		if ($this->queryData["type"] == "insert") {
			$query .= "($keys) VALUES ($values)";
		}
		$this->bind($dataToBind);
		$this->queryData["query"] .= $query;
		$this->queryData["values"] = $params;
		return $this;
	}

	/**
	 * Fetch a specific number
	 * 
	 * @param mixed $limit The number of rows to fetch
	 */
	public function limit($limit) : self
	{
		$this->queryData["query"] .= " LIMIT $limit";
		return $this;
	}

	/**
	 * Make sure a value doesn't already exist in a table to avoid duplicates.
	 */
	public function unique(...$uniques) {
		$uniqueParams = [];
		foreach ($uniques as $unique) {
			if (is_array($unique)) {
				foreach ($unique as $param) {
					$uniqueParams[] = $param;
				}
			} else {
				$uniqueParams[] = $unique;
			}
		}
		$this->queryData["uniques"] = $uniqueParams;
		return $this;
	}

	/**
	 * Bind parameters to a query
	 * 
	 * @param array|string $data The data to bind to string
	 * @param string $type The type of the value (s, i, b)
	 */
	public function bind($data, $type = "s") : self
	{
		$params = [];

		if (is_array($data)) {
			foreach ($data as $param => $type) {
				$params[] = [$param, $type];
			}
		} else {
			$params[] = [$data, $type];
		}
		
		$this->queryData["params"] = $params;

		return $this;
	}

	/**
	 * Execute a query
	 */
	public function execute() {
		$query = $this->queryData["query"];
		$params = $this->queryData["params"];
		$uniques = $this->queryData["uniques"];

		if (count($uniques) > 0) {
			// make sure no duplicates get inserted
			// foreach ($uniques as $unique) {
			// 	if (!isset($items[$unique])) {
			// 		$this->response->respond(["error" => "$unique not found, Add $unique to your \$db->add items or check your spelling."]);
			// 		exit();
			// 	} else {
			// 		if ($this->select($table, "*", "$unique = ?", [$items[$unique]])->fetchObj()) {
			// 			$this->form->errorsArray[$unique] = "$unique already exists";
			// 		}
			// 	}
			// }
		}

		$types = "";
		$bindings = [];

		foreach ($params as $data) {
			$types .= $data[1];
			$bindings[] = $data[0];
		}

		if (!$types) $types = str_repeat('s', count($bindings));
		
		if (!$bindings) {
			$this->queryResult = $this->connection->query($query);
		} else {
			// $this->response->throwErr([$query, $bindings, $types]);
			$stmt = $this->stmt = $this->connection->prepare($query);
			$stmt->bind_param($types, ...$bindings);
			$stmt->execute();
			$this->queryResult = $stmt->get_result();
		}
	}

	/**
	 * Get number of rows from SELECT
	 *
	 * @return int $connection->num_rows
	 */
	public function count() : int
	{
		$this->execute();
		return mysqli_num_rows($this->queryResult);
	}

	/**
	 * Fetch query results as an associative array
	 */
	public function fetchAssoc()
	{
		$this->execute();
		return mysqli_fetch_assoc($this->queryResult);
	}

	/**
	 * Fetch query results as object
	 */
	public function fetchObj()
	{
		$this->execute();
		return mysqli_fetch_object($this->queryResult);
	}

	/**
	 * Fetch all
	 */
	public function fetchAll($type = MYSQLI_NUM)
	{
		$this->execute();
		if ($type != MYSQLI_NUM || $type != "obj") {
			$type = \MYSQLI_ASSOC;
		}
		return mysqli_fetch_all($this->queryResult, $type);
	}

	/**
	 * Return caught errors if any
	 */
	public function errors() : array
	{
		return $this->errorsArray;
	}
}
