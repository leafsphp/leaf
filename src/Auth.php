<?php

namespace Leaf;

use Leaf\Helpers\Authentication;
use Leaf\Helpers\Password;

/**
 * Leaf Simple Auth
 * ---------------
 * Perform simple authentication tasks.
 * 
 * @author Michael Darko
 * @since 1.5.0
 */
class Auth
{
	/**
	 * All errors caught
	 */
	protected $errorsArray = [];

	/**
	 * Token secret
	 */
	protected $secretKey = "TOKEN_SECRET";

	/**
	 * Token Lifetime
	 */
	protected $lifeTime = null;

	/**
	 * Auth Settings
	 */
	protected $settings = [
		"USE_TIMESTAMPS" => true,
		"PASSWORD_ENCODE" => null,
		"PASSWORD_VERIFY" => null,
		"PASSWORD_KEY" => "password",
		"HIDE_ID" => true,
		"HIDE_PASSWORD" => true,
		"LOGIN_PARAMS_ERROR" => "Incorrect credentials!",
		"LOGIN_PASSWORD_ERROR" => "Password is incorrect!",
	];

	/**
	 * @var \Leaf\Db
	 */
	public $db;

	public function __construct()
	{
		$this->form = new Form;
		$this->db = new Db;
	}

	/**
	 * Create a db connection
	 * 
	 * @param string $host The db host name
	 * @param string $host The db user
	 * @param string $host The db password
	 * @param string $host The db name
	 */
	public function connect(string $host, string $user, string $password, string $dbname): void
	{
		$this->db->connect($host, $user, $password, $dbname);
	}

	/**
	 * Create a database connection from env variables
	 */
	public function autoConnect(): void
	{
		$this->connect(
			getenv("DB_HOST"),
			getenv("DB_USERNAME"),
			getenv("DB_PASSWORD"),
			getenv("DB_DATABASE")
		);
	}

	/**
	 * Get or set the default token lifetime value
	 * 
	 * @param int $lifeTime The new lifetime value for token
	 * 
	 * @return int|string|void
	 */
	public function tokenLifetime($lifeTime = null)
	{
		if (!$lifeTime) return $this->lifeTime;

		$this->lifeTime = $lifeTime;
	}

	/**
	 * Set token secret key for auth
	 * 
	 * @param string $secretKey
	 */
	public function setSecretKey(string $secretKey)
	{
		$this->secretKey = $secretKey;
	}

	/**
	 * Get auth secret key
	 */
	public function getSecretKey()
	{
		return $this->secretKey;
	}

	/**
	 * Set auth config
	 */
	public function config($config, $value = "")
	{
		if (is_array($config)) {
			foreach ($config as $key => $configValue) {
				$this->config($key, $configValue);
			}
		} else {
			$this->settings[$config] = $value;
		}
	}

	/**
	 * Simple user login
	 * 
	 * @param string table: Table to look for users
	 * @param array $credentials User credentials
	 * @param array $validate Validation for parameters
	 * 
	 * @return array user: all user info + tokens + session data
	 */
	public function login(string $table, array $credentials, array $validate = [])
	{
		$passKey = $this->settings["PASSWORD_KEY"];
		$password = $credentials[$passKey];

		if (isset($credentials[$passKey])) {
			unset($credentials[$passKey]);
		}

		$user = $this->db->select($table)->where($credentials)->validate($validate)->fetchAssoc();
		if (!$user) {
			$this->errorsArray["auth"] = $this->settings["LOGIN_PARAMS_ERROR"];
			return null;
		}

		$passwordIsValid = true;

		if ($this->settings["PASSWORD_VERIFY"] !== false && isset($user[$passKey])) {
			if (is_callable($this->settings["PASSWORD_VERIFY"])) {
				$passwordIsValid = call_user_func($this->settings["PASSWORD_VERIFY"], $password, $user[$passKey]);
			} else if ($this->settings["PASSWORD_VERIFY"] === Password::MD5) {
				$passwordIsValid = (md5($password) === $user[$passKey]);
			} else {
				$passwordIsValid = Password::verify($password, $user[$passKey]);
			}
		}

		if (!$passwordIsValid) {
			$this->errorsArray["password"] = $this->settings["LOGIN_PASSWORD_ERROR"];
			return null;
		}

		$token = Authentication::generateSimpleToken($user["id"], $this->secretKey, $this->lifeTime);

		if ($this->settings["HIDE_ID"]) {
			unset($user["id"]);
		}

		if ($this->settings["HIDE_PASSWORD"] && (isset($user[$passKey]) || !$user[$passKey])) {
			unset($user[$passKey]);
		}

		if (!$token) {
			$this->errorsArray = array_merge($this->errorsArray, Authentication::errors());
			return null;
		}

		$response["user"] = $user;
		$response["token"] = $token;

		return $response;
	}

	/**
	 * Simple user registration
	 * 
	 * @param string $table: Table to store user in
	 * @param array $credentials Information for new user
	 * @param array $uniques Parameters which should be unique
	 * @param array $validate Validation for parameters
	 * 
	 * @return array user: all user info + tokens + session data
	 */
	public function register(string $table, array $credentials, array $uniques = [], array $validate = [])
	{
		$passKey = $this->settings["PASSWORD_KEY"];

		if ($this->settings["PASSWORD_ENCODE"] !== false && isset($credentials[$passKey])) {
			if (is_callable($this->settings["PASSWORD_ENCODE"])) {
				$credentials[$passKey] = call_user_func($this->settings["PASSWORD_ENCODE"], $credentials[$passKey]);
			} else if ($this->settings["PASSWORD_ENCODE"] === "md5") {
				$credentials[$passKey] = md5($credentials[$passKey]);
			} else {
				$credentials[$passKey] = Password::hash($credentials[$passKey]);
			}
		}

		if ($this->settings["USE_TIMESTAMPS"]) {
			$now = Date::now();
			$credentials["created_at"] = $now;
			$credentials["updated_at"] = $now;
		}

		try {
			$query = $this->db->insert($table)->params($credentials)->unique($uniques)->validate($validate)->execute();
		} catch (\Throwable $th) {
			$this->errorsArray["dev"] = $th->getMessage();
			return null;
		}

		if (!$query) {
			$this->errorsArray = array_merge($this->errorsArray, $this->db->errors());
			return null;
		}

		$user = $this->db->select($table)->where($credentials)->validate($validate)->fetchAssoc();

		if (!$user) {
			$this->errorsArray = array_merge($this->errorsArray, $this->db->errors());
			return null;
		}

		$token = Authentication::generateSimpleToken($user["id"], $this->secretKey, $this->lifeTime);

		if ($this->settings["HIDE_ID"]) {
			unset($user["id"]);
		}

		if ($this->settings["HIDE_PASSWORD"] && (isset($user[$passKey]) || !$user[$passKey])) {
			unset($user[$passKey]);
		}

		if (!$token) {
			$this->errorsArray = array_merge($this->errorsArray, Authentication::errors());
			return null;
		}

		$response["user"] = $user;
		$response["token"] = $token;

		return $response;
	}

	/**
	 * Simple user update
	 * 
	 * @param string $table: Table to store user in
	 * @param array $credentials New information for user
	 * @param array $where Information to find user by
	 * @param array $uniques Parameters which should be unique
	 * @param array $validate Validation for parameters
	 * 
	 * @return array user: all user info + tokens + session data
	 */
	public function update(string $table, array $credentials, array $where, array $uniques = [], array $validate = [])
	{
		$passKey = $this->settings["PASSWORD_KEY"];

		if ($this->settings["PASSWORD_ENCODE"] !== false && isset($credentials[$passKey])) {
			if (is_callable($this->settings["PASSWORD_ENCODE"])) {
				$credentials[$passKey] = call_user_func($this->settings["PASSWORD_ENCODE"], $credentials[$passKey]);
			} else if ($this->settings["PASSWORD_ENCODE"] === "md5") {
				$credentials[$passKey] = md5($credentials[$passKey]);
			} else {
				$credentials[$passKey] = Password::hash($credentials[$passKey]);
			}
		}

		if ($this->settings["USE_TIMESTAMPS"]) {
			$credentials["updated_at"] = Date::now();
		}

		if (count($uniques) > 0) {
			foreach ($uniques as $unique) {
				if (!isset($credentials[$unique])) {
					(new Http\Response)->throwErr(["error" => "$unique not found in credentials."]);
				}

				$data = $this->db->select($table)->where($unique, $credentials[$unique])->fetchAssoc();

				$wKeys = array_keys($where);
				$wValues = array_values($where);

				if (isset($data[$wKeys[0]]) && $data[$wKeys[0]] != $wValues[0]) {
					$this->errorsArray[$unique] = "$unique already exists";
				}
			}

			if (count($this->errorsArray) > 0) return null;
		}

		try {
			$query = $this->db->update($table)->params($credentials)->where($where)->validate($validate)->execute();
		} catch (\Throwable $th) {
			$this->errorsArray["dev"] = $th->getMessage();
			return null;
		}

		if (!$query) {
			$this->errorsArray = array_merge($this->errorsArray, $this->db->errors());
			return null;
		}

		unset($credentials["updated_at"]);

		$user = $this->db->select($table)->where($credentials)->validate($validate)->fetchAssoc();
		if (!$user) {
			$this->errorsArray = array_merge($this->errorsArray, $this->db->errors());
			return null;
		}

		$token = Authentication::generateSimpleToken($user["id"], $this->secretKey, $this->lifeTime);

		if ($this->settings["HIDE_ID"]) {
			unset($user["id"]);
		}

		if ($this->settings["HIDE_PASSWORD"] && (isset($user[$passKey]) || !$user[$passKey])) {
			unset($user[$passKey]);
		}

		if (!$token) {
			$this->errorsArray = array_merge($this->errorsArray, Authentication::errors());
			return null;
		}

		$response["user"] = $user;
		$response["token"] = $token;

		return $response;
	}

	/**
	 * Validate Json Web Token
	 * 
	 * @param string $token The token validate
	 * @param string $secretKey The secret key used to encode token
	 */
	public function validate($token, $secretKey = null)
	{
		$payload = Authentication::validate($token, $secretKey ?? $this->secretKey);
		if ($payload) return $payload;

		$this->errorsArray = array_merge($this->errorsArray, Authentication::errors());

		return null;
	}

	/**
	 * Validate Bearer Token
	 * 
	 * @param string $secretKey The secret key used to encode token
	 */
	public function validateToken($secretKey = null)
	{
		$payload = Authentication::validateToken($secretKey ?? $this->secretKey);
		if ($payload) return $payload;

		$this->errorsArray = array_merge($this->errorsArray, Authentication::errors());

		return null;
	}

	/**
	 * Get Bearer token
	 */
	public function getBearerToken()
	{
		$token = Authentication::getBearerToken();
		if ($token) return $token;

		$this->errorsArray = array_merge($this->errorsArray, Authentication::errors());

		return null;
	}

	/**
	 * Get the current user data from token
	 * 
	 * @param string $table The table to look for user
	 * @param array $hidden Fields to hide from user array
	 */
	public function user($table = "users", $hidden = [])
	{
		if (!$this->id()) return null;

		$user = $this->db->select($table)->where("id", $this->id())->fetchAssoc();

		if (count($hidden) > 0) {
			foreach ($hidden as $item) {
				if (isset($user[$item]) || !$user[$item]) {
					unset($user[$item]);
				}
			}
		}

		return $user;
	}

	/**
	 * Return the user id encoded in token 
	 */
	public function id()
	{
		$payload = $this->validateToken($this->getSecretKey());
		if (!$payload) return null;
		return $payload->user_id;
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
