<?php

namespace Leaf;

use \Leaf\Form;
use \Leaf\Http\Response;
use \Leaf\Helpers\Authentication;

/**
 * Leaf Simple Auth
 * ---------------
 * Perform simple authentication tasks.
 */
class Auth
{
	protected $errorsArray = [];
	protected $secret_key = "TOKEN_SECRET";

	public function __construct()
	{
		$this->form = new Form;
		$this->response = new Response;
		$this->token = new Authentication;
		$this->db = new Db;
	}

	public function connect(string $host, string $user, string $password, string $dbname): void
	{
		$this->db->connect($host, $user, $password, $dbname);
	}

	public function auto_connect(): void
	{
		$this->connect(
			getenv("DB_HOST"),
			getenv("DB_USERNAME"),
			getenv("DB_PASSWORD"),
			getenv("DB_DATABASE")
		);
	}

	/**
	 * Set token secret key for auth
	 */
	public function setSecretKey(string $secret_key)
	{
		$this->secret_key = $secret_key;
	}

	/**
	 * Get auth secret key
	 */
	public function getSecretKey()
	{
		return $this->secret_key;
	}

	/**
	 * Simple user login
	 * 
	 * @param string table: Table to look for users
	 * @param array $credentials User credentials
	 * @param string password_encode: Password encode type, should match password
	 * @param array $validate Validation for parameters
	 * 
	 * @return array user: all user info + tokens + session data
	 */
	public function login(string $table, array $credentials, string $password_encode = null, array $validate = [])
	{
		if ($password_encode == "md5" && isset($credentials["password"])) {
			$credentials["password"] = md5($credentials["password"]);
		}

		$user = $this->db->select($table)->where($credentials)->validate($validate)->fetchAssoc();
		if (!$user) {
			$this->errorsArray["auth"] = "Incorrect credentials, please check and try again";
			return false;
		}

		$token = $this->token->generateSimpleToken($user["id"], $this->secret_key);
		unset($user["id"]);
		if (isset($user["password"])) unset($user["password"]);
		if ($token == false) {
			foreach ($this->token->errors() as $key => $value) {
				$this->errorsArray[$key] = $value;
			}
			return false;
		}
		$user["token"] = $token;

		return $user;
	}

	/**
	 * Simple user registration
	 * 
	 * @param string $table: Table to store user in
	 * @param array $credentials Information for new user
	 * @param array $uniques Parameters which should be unique
	 * @param string password_encode: Password encode type, should match password
	 * @param array $validate Validation for parameters
	 * 
	 * @return array user: all user info + tokens + session data
	 */
	public function register(string $table, array $credentials, array $uniques = [], string $password_encode = null, array $validate = [])
	{
		if ($password_encode == "md5" && isset($credentials["password"])) {
			$credentials["password"] = md5($credentials["password"]);
		}

		try {
			if ($this->db->insert($table)->params($credentials)->unique($uniques)->validate($validate)->execute() === false) {
				foreach ($this->db->errors() as $key => $value) {
					$this->errorsArray[$key] = $value;
				}
				return false;
			}
		} catch (\Throwable $th) {
			$this->errorsArray["error"] = $th->getMessage();
			return false;
		}

		return $this->login($table, $credentials);
	}

	/**
	 * Validate Json Web Token
	 */
	public function validate($token, $secret_key)
	{
		$payload = $this->token->validate($token, $secret_key);

		if ($payload == false) {
			foreach ($this->token->errors() as $key => $value) {
				$this->errorsArray[$key] = $value;
			}
			return false;
		}

		return $payload;
	}

	/**
	 * Validate Bearer Token
	 */
	public function validateToken()
	{
		$payload = $this->token->validateToken($this->secret_key);

		if ($payload == false) {
			foreach ($this->token->errors() as $key => $value) {
				$this->errorsArray[$key] = $value;
			}
			return false;
		}

		return $payload;
	}

	/**
	 * Get Bearer token
	 */
	public function getBearerToken()
	{
		$token = $this->token->getBearerToken();

		if ($token == false) {
			foreach ($this->token->errors() as $key => $value) {
				$this->errorsArray[$key] = $value;
			}
			return false;
		}

		return $token;
	}

	/**
	 * Get the current user data
	 */
	public function currentUser($table)
	{
		$payload = $this->validateToken();
		if (!$payload) return false;
		return $this->login($table, ["id" => $payload->user_id]);
	}

	/**
	 * Return form field
	 */
	public function get($param)
	{
		return $this->form->get($param);
	}

	/**
	 * Get all authentication errors as associative array
	 */
	public function errors(): array
	{
		return $this->errorsArray;
	}
}
