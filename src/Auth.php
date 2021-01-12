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
	 * @var \Leaf\Http\Session
	 */
	protected $session;

	/**
	 * All defined session middleware
	 */
	protected $middleware = [];

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
		"USE_SESSION" => false,
		"SESSION_ON_REGISTER" => false,
		"GUARD_LOGIN" => "/auth/login",
		"GUARD_REGISTER" => "/auth/register",
		"GUARD_HOME" => "/home",
		"SAVE_SESSION_JWT" => false,
	];

	/**
	 * @var \Leaf\Db
	 */
	public $db;

	public function __construct($useSession = false)
	{
		$this->form = new Form;
		$this->db = new Db;

		if ($useSession) {
			$this->useSession();
		}
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
	public function config($config, $value = null)
	{
		if (is_array($config)) {
			foreach ($config as $key => $configValue) {
				$this->config($key, $configValue);
			}
		} else {
			if (!$value) return $this->settings[$config] ?? null;
			$this->settings[$config] = $value;
		}
	}

	/**
	 * Exception for experimental features
	 */
	protected function experimental($method)
	{
		if (!$this->config("USE_SESSION")) {
			trigger_error("Auth::$method is experimental. Turn on USE_SESSION to use this feature.");
		}
	}

	/**
	 * Manually start an auth session
	 */
	public function useSession()
	{
		$this->session = new \Leaf\Http\Session(false);
		$this->config("USE_SESSION", true);

		session_start();

		if (!$this->session->get("SESSION_STARTED_AT")) {
			$this->session->set("SESSION_STARTED_AT", time());
		}

		$this->session->set("SESSION_LAST_ACTIVITY", time());
	}

	/**
	 * Session Length
	 */
	public function sessionLength()
	{
		$this->experimental("sessionLength");

		return time() - $this->session->get("SESSION_STARTED_AT");
	}

	/**
	 * Session last active
	 */
	public function sessionActive()
	{
		$this->experimental("sessionActive");

		return time() - $this->session->get("SESSION_LAST_ACTIVITY");
	}

	/**
	 * Refresh session
	 */
	public function refresh($clearData = true)
	{
		$this->experimental("refresh");

		$success = $this->session->regenerate($clearData);

		$this->session->set("SESSION_STARTED_AT", time());
		$this->session->set("SESSION_LAST_ACTIVITY", time());
		$this->session->set("AUTH_SESISON", true);

		return $success;
	}

	/**
	 * Define/Return session middleware
	 * 
	 * **This method only works with session auth**
	 */
	public function middleware(string $name, callable $handler = null)
	{
		$this->experimental("middleware");

		if (!$handler) return $this->middleware[$name];

		$this->middleware[$name] = $handler;
	}

	/**
	 * Check session status
	 */
	public function session()
	{
		$this->experimental("session");

		return $this->session->get("AUTH_USER") ?? false;
	}

	/**
	 * End a session
	 */
	public function endSession($location = null)
	{
		$this->experimental("endSession");

		$this->session->destroy();
	
		if ($location) {
			$route = $this->config($location) ?? $location;
			(new Http\Response)->redirect($route);
		}
	}

	/**
	 * A simple auth guard: 'guest' pages can't be viewed when logged in,
	 * 'auth' pages can't be viewed without authentication
	 * 
	 * @param array|string $type The type of guard/guard options
	 */
	public function guard($type)
	{
		$this->experimental("guard");

		if (is_array($type)) {
			if (isset($type["hasAuth"])) {
				$type = $type["hasAuth"] ? 'auth' : 'guest';
			}
		}

		if ($type === 'guest' && $this->session()) {
			exit(header("location: " . $this->config("GUARD_HOME"), true, 302));
		}

		if ($type === 'auth' && !$this->session()) {
			exit(header("location: " . $this->config("GUARD_LOGIN"), true, 302));
		}
	}

	/**
	 * Save some data to auth session
	 */
	protected function saveToSession($key, $data)
	{
		$this->experimental("saveToSession");

		$this->session->set($key, $data);
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

		if (isset($user["id"])) {
			$userId = $user["id"];
		}

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

		if ($this->config("USE_SESSION")) {
			if (isset($userId)) {
				$user["id"] = $userId;
			}

			$this->saveToSession("AUTH_USER", $user);
			$this->saveToSession("HAS_SESSION", true);

			if ($this->config("SAVE_SESSION_JWT")) {
				$this->saveToSession("AUTH_TOKEN", $token);
			}

			exit(header("location: " . $this->config("GUARD_HOME")));
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

		if (isset($user["id"])) {
			$userId = $user["id"];
		}

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

		if ($this->config("USE_SESSION")) {
			if ($this->config("SESSION_ON_REGISTER")) {
				if (isset($userId)) {
					$user["id"] = $userId;
				}

				$this->saveToSession("AUTH_USER", $user);
				$this->saveToSession("HAS_SESSION", true);

				if ($this->config("SAVE_SESSION_JWT")) {
					$this->saveToSession("AUTH_TOKEN", $token);
				}

				exit(header("location: " . $this->config("GUARD_HOME")));
			} else {
				exit(header("location: " . $this->config("GUARD_LOGIN")));
			}
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

		if (isset($credentials["updated_at"])) {
			unset($credentials["updated_at"]);
		}

		$user = $this->db->select($table)->where($credentials)->validate($validate)->fetchAssoc();
		if (!$user) {
			$this->errorsArray = array_merge($this->errorsArray, $this->db->errors());
			return null;
		}

		$token = Authentication::generateSimpleToken($user["id"], $this->secretKey, $this->lifeTime);

		if (isset($user["id"])) {
			$userId = $user["id"];
		}

		if ($this->settings["HIDE_ID"] && isset($user["id"])) {
			unset($user["id"]);
		}

		if ($this->settings["HIDE_PASSWORD"] && (isset($user[$passKey]) || !$user[$passKey])) {
			unset($user[$passKey]);
		}

		if (!$token) {
			$this->errorsArray = array_merge($this->errorsArray, Authentication::errors());
			return null;
		}

		if ($this->config("USE_SESSION")) {
			if (isset($userId)) {
				$user["id"] = $userId;
			}

			$this->saveToSession("AUTH_USER", $user);
			$this->saveToSession("HAS_SESSION", true);

			if ($this->config("SAVE_SESSION_JWT")) {
				$this->saveToSession("AUTH_TOKEN", $token);
			}

			return $user;
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
		if (!$this->id()) {
			if ($this->config("USE_SESSION")) {
				return $this->session->get("AUTH_USER");
			}

			return null;
		}

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
		if ($this->config("USE_SESSION")) {
			return $this->session->get("AUTH_USER")["id"] ?? null;
		}

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
