<?php

namespace Leaf;

use Leaf\Helpers\Authentication;
use Leaf\Helpers\Password;

/**
 * Leaf Simple Auth
 * -------------------------
 * Authentication made easy.
 *
 * @author Michael Darko
 * @since 1.5.0
 * @version 2.0.0
 */
class Auth
{
	/**
	 * All errors caught
	 */
	protected static $errorsArray = [];

	/**
	 * Token secret
	 */
	protected static $secretKey = "TOKEN_SECRET";

	/**
	 * Token Lifetime
	 */
	protected static $lifeTime = null;

	/**
	 * @var \Leaf\Http\Session
	 */
	protected static $session;

	/**
	 * All defined session middleware
	 */
	protected static $middleware = [];

	/**
	 * Auth Settings
	 */
	protected static $settings = [
		"ID_KEY" => "id",
		"USE_UUID" => false,
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
	public static $db;

	/**
	 * @var \Leaf\Form
	 */
	public static $form;

	public function __construct($useSession = false)
	{
		static::$form = new Form;
		static::$db = new Db;

		if ($useSession) {
			static::$useSession();
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
	public static function connect(string $host, string $user, string $password, string $dbname): void
	{
		static::$form = new Form;
		static::$db = new Db;

		static::$db->connect($host, $user, $password, $dbname);
	}

	/**
	 * Create a database connection from env variables
	 */
	public static function autoConnect(): void
	{
		static::connect(
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
	public static function tokenLifetime($lifeTime = null)
	{
		if (!$lifeTime) return static::$lifeTime;

		static::$lifeTime = $lifeTime;
	}

	/**
	 * Set token secret key for auth
	 *
	 * @param string $secretKey
	 */
	public static function setSecretKey(string $secretKey)
	{
		static::$secretKey = $secretKey;
	}

	/**
	 * Get auth secret key
	 */
	public static function getSecretKey()
	{
		return static::$secretKey;
	}

	/**
	 * Set auth config
	 */
	public static function config($config, $value = null)
	{
		if (is_array($config)) {
			foreach ($config as $key => $configValue) {
				static::config($key, $configValue);
			}
		} else {
			if (!$value) return static::$settings[$config] ?? null;
			static::$settings[$config] = $value;
		}
	}

	/**
	 * Exception for experimental features
	 */
	protected static function experimental($method)
	{
		if (!static::config("USE_SESSION")) {
			trigger_error("Auth::$method is experimental. Turn on USE_SESSION to use this feature.");
		}
	}

	/**
	 * Manually start an auth session
	 */
	public static function useSession()
	{
		static::$session = new \Leaf\Http\Session(false);
		static::config("USE_SESSION", true);

		session_start();

		if (!static::$session->get("SESSION_STARTED_AT")) {
			static::$session->set("SESSION_STARTED_AT", time());
		}

		static::$session->set("SESSION_LAST_ACTIVITY", time());
	}

	/**
	 * Session Length
	 */
	public static function sessionLength()
	{
		static::experimental("sessionLength");

		return time() - static::$session->get("SESSION_STARTED_AT");
	}

	/**
	 * Session last active
	 */
	public static function sessionActive()
	{
		static::experimental("sessionActive");

		return time() - static::$session->get("SESSION_LAST_ACTIVITY");
	}

	/**
	 * Refresh session
	 */
	public static function refresh($clearData = true)
	{
		static::experimental("refresh");

		$success = static::$session->regenerate($clearData);

		static::$session->set("SESSION_STARTED_AT", time());
		static::$session->set("SESSION_LAST_ACTIVITY", time());
		static::$session->set("AUTH_SESISON", true);

		return $success;
	}

	/**
	 * Define/Return session middleware
	 *
	 * **This method only works with session auth**
	 */
	public static function middleware(string $name, callable $handler = null)
	{
		static::experimental("middleware");

		if (!$handler) return static::$middleware[$name];

		static::$middleware[$name] = $handler;
	}

	/**
	 * Check session status
	 */
	public static function session()
	{
		static::experimental("session");

		return static::$session->get("AUTH_USER") ?? false;
	}

	/**
	 * End a session
	 */
	public static function endSession($location = null)
	{
		static::experimental("endSession");

		static::$session->destroy();

		if ($location) {
			$route = static::config($location) ?? $location;
			(new Http\Response)->redirect($route);
		}
	}

	/**
	 * A simple auth guard: 'guest' pages can't be viewed when logged in,
	 * 'auth' pages can't be viewed without authentication
	 *
	 * @param array|string $type The type of guard/guard options
	 */
	public static function guard($type)
	{
		static::experimental("guard");

		if (is_array($type)) {
			if (isset($type["hasAuth"])) {
				$type = $type["hasAuth"] ? 'auth' : 'guest';
			}
		}

		if ($type === 'guest' && static::session()) {
			exit(header("location: " . static::config("GUARD_HOME"), true, 302));
		}

		if ($type === 'auth' && !static::session()) {
			exit(header("location: " . static::config("GUARD_LOGIN"), true, 302));
		}
	}

	/**
	 * Save some data to auth session
	 */
	protected static function saveToSession($key, $data)
	{
		static::experimental("saveToSession");

		static::$session->set($key, $data);
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
	public static function login(string $table, array $credentials, array $validate = [])
	{
		$passKey = static::$settings["PASSWORD_KEY"];
		$password = $credentials[$passKey];

		if (isset($credentials[$passKey])) {
			unset($credentials[$passKey]);
		}

		$user = static::$db->select($table)->where($credentials)->validate($validate)->fetchAssoc();

		if (!$user) {
			static::$errorsArray["auth"] = static::$settings["LOGIN_PARAMS_ERROR"];
			return null;
		}

		$passwordIsValid = true;

		if (static::$settings["PASSWORD_VERIFY"] !== false && isset($user[$passKey])) {
			if (is_callable(static::$settings["PASSWORD_VERIFY"])) {
				$passwordIsValid = call_user_func(static::$settings["PASSWORD_VERIFY"], $password, $user[$passKey]);
			} else if (static::$settings["PASSWORD_VERIFY"] === Password::MD5) {
				$passwordIsValid = (md5($password) === $user[$passKey]);
			} else {
				$passwordIsValid = Password::verify($password, $user[$passKey]);
			}
		}

		if (!$passwordIsValid) {
			static::$errorsArray["password"] = static::$settings["LOGIN_PASSWORD_ERROR"];
			return null;
		}

		$token = Authentication::generateSimpleToken(
			$user[static::$settings["ID_KEY"]],
			static::$secretKey,
			static::$lifeTime
		);

		if (isset($user[static::$settings["ID_KEY"]])) {
			$userId = $user[static::$settings["ID_KEY"]];
		}

		if (static::$settings["HIDE_ID"]) {
			unset($user[static::$settings["ID_KEY"]]);
		}

		if (static::$settings["HIDE_PASSWORD"] && (isset($user[$passKey]) || !$user[$passKey])) {
			unset($user[$passKey]);
		}

		if (!$token) {
			static::$errorsArray = array_merge(static::$errorsArray, Authentication::errors());
			return null;
		}

		if (static::config("USE_SESSION")) {
			if (isset($userId)) {
				$user[static::$settings["ID_KEY"]] = $userId;
			}

			static::saveToSession("AUTH_USER", $user);
			static::saveToSession("HAS_SESSION", true);

			if (static::config("SAVE_SESSION_JWT")) {
				static::saveToSession("AUTH_TOKEN", $token);
			}

			exit(header("location: " . static::config("GUARD_HOME")));
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
	public static function register(string $table, array $credentials, array $uniques = [], array $validate = [])
	{
		$passKey = static::$settings["PASSWORD_KEY"];

		if (static::$settings["PASSWORD_ENCODE"] !== false && isset($credentials[$passKey])) {
			if (is_callable(static::$settings["PASSWORD_ENCODE"])) {
				$credentials[$passKey] = call_user_func(static::$settings["PASSWORD_ENCODE"], $credentials[$passKey]);
			} else if (static::$settings["PASSWORD_ENCODE"] === "md5") {
				$credentials[$passKey] = md5($credentials[$passKey]);
			} else {
				$credentials[$passKey] = Password::hash($credentials[$passKey]);
			}
		}

		if (static::$settings["USE_TIMESTAMPS"]) {
			$now = Date::now();
			$credentials["created_at"] = $now;
			$credentials["updated_at"] = $now;
		}

		if (static::$settings["USE_UUID"] !== false) {
			$credentials[static::$settings["ID_KEY"]] = static::$settings["USE_UUID"];
		}

		try {
			$query = static::$db->insert($table)->params($credentials)->unique($uniques)->validate($validate)->execute();
		} catch (\Throwable $th) {
			static::$errorsArray["dev"] = $th->getMessage();
			return null;
		}

		if (!$query) {
			static::$errorsArray = array_merge(static::$errorsArray, static::$db->errors());
			return null;
		}

		$user = static::$db->select($table)->where($credentials)->validate($validate)->fetchAssoc();

		if (!$user) {
			static::$errorsArray = array_merge(static::$errorsArray, static::$db->errors());
			return null;
		}

		$token = Authentication::generateSimpleToken($user[static::$settings["ID_KEY"]], static::$secretKey, static::$lifeTime);

		if (isset($user[static::$settings["ID_KEY"]])) {
			$userId = $user[static::$settings["ID_KEY"]];
		}

		if (static::$settings["HIDE_ID"]) {
			unset($user[static::$settings["ID_KEY"]]);
		}

		if (static::$settings["HIDE_PASSWORD"] && (isset($user[$passKey]) || !$user[$passKey])) {
			unset($user[$passKey]);
		}

		if (!$token) {
			static::$errorsArray = array_merge(static::$errorsArray, Authentication::errors());
			return null;
		}

		if (static::config("USE_SESSION")) {
			if (static::config("SESSION_ON_REGISTER")) {
				if (isset($userId)) {
					$user[static::$settings["ID_KEY"]] = $userId;
				}

				static::saveToSession("AUTH_USER", $user);
				static::saveToSession("HAS_SESSION", true);

				if (static::config("SAVE_SESSION_JWT")) {
					static::saveToSession("AUTH_TOKEN", $token);
				}

				exit(header("location: " . static::config("GUARD_HOME")));
			} else {
				exit(header("location: " . static::config("GUARD_LOGIN")));
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
	public static function update(string $table, array $credentials, array $where, array $uniques = [], array $validate = [])
	{
		$passKey = static::$settings["PASSWORD_KEY"];

		if (static::$settings["PASSWORD_ENCODE"] !== false && isset($credentials[$passKey])) {
			if (is_callable(static::$settings["PASSWORD_ENCODE"])) {
				$credentials[$passKey] = call_user_func(static::$settings["PASSWORD_ENCODE"], $credentials[$passKey]);
			} else if (static::$settings["PASSWORD_ENCODE"] === "md5") {
				$credentials[$passKey] = md5($credentials[$passKey]);
			} else {
				$credentials[$passKey] = Password::hash($credentials[$passKey]);
			}
		}

		if (static::$settings["USE_TIMESTAMPS"]) {
			$credentials["updated_at"] = Date::now();
		}

		if (count($uniques) > 0) {
			foreach ($uniques as $unique) {
				if (!isset($credentials[$unique])) {
					(new Http\Response)->throwErr(["error" => "$unique not found in credentials."]);
				}

				$data = static::$db->select($table)->where($unique, $credentials[$unique])->fetchAssoc();

				$wKeys = array_keys($where);
				$wValues = array_values($where);

				if (isset($data[$wKeys[0]]) && $data[$wKeys[0]] != $wValues[0]) {
					static::$errorsArray[$unique] = "$unique already exists";
				}
			}

			if (count(static::$errorsArray) > 0) return null;
		}

		try {
			$query = static::$db->update($table)->params($credentials)->where($where)->validate($validate)->execute();
		} catch (\Throwable $th) {
			static::$errorsArray["dev"] = $th->getMessage();
			return null;
		}

		if (!$query) {
			static::$errorsArray = array_merge(static::$errorsArray, static::$db->errors());
			return null;
		}

		if (isset($credentials["updated_at"])) {
			unset($credentials["updated_at"]);
		}

		$user = static::$db->select($table)->where($credentials)->validate($validate)->fetchAssoc();
		if (!$user) {
			static::$errorsArray = array_merge(static::$errorsArray, static::$db->errors());
			return null;
		}

		$token = Authentication::generateSimpleToken($user[static::$settings["ID_KEY"]], static::$secretKey, static::$lifeTime);

		if (isset($user[static::$settings["ID_KEY"]])) {
			$userId = $user[static::$settings["ID_KEY"]];
		}

		if (static::$settings["HIDE_ID"] && isset($user[static::$settings["ID_KEY"]])) {
			unset($user[static::$settings["ID_KEY"]]);
		}

		if (static::$settings["HIDE_PASSWORD"] && (isset($user[$passKey]) || !$user[$passKey])) {
			unset($user[$passKey]);
		}

		if (!$token) {
			static::$errorsArray = array_merge(static::$errorsArray, Authentication::errors());
			return null;
		}

		if (static::config("USE_SESSION")) {
			if (isset($userId)) {
				$user[static::$settings["ID_KEY"]] = $userId;
			}

			static::saveToSession("AUTH_USER", $user);
			static::saveToSession("HAS_SESSION", true);

			if (static::config("SAVE_SESSION_JWT")) {
				static::saveToSession("AUTH_TOKEN", $token);
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
	public static function validate($token, $secretKey = null)
	{
		$payload = Authentication::validate($token, $secretKey ?? static::$secretKey);
		if ($payload) return $payload;

		static::$errorsArray = array_merge(static::$errorsArray, Authentication::errors());

		return null;
	}

	/**
	 * Validate Bearer Token
	 *
	 * @param string $secretKey The secret key used to encode token
	 */
	public static function validateToken($secretKey = null)
	{
		$payload = Authentication::validateToken($secretKey ?? static::$secretKey);
		if ($payload) return $payload;

		static::$errorsArray = array_merge(static::$errorsArray, Authentication::errors());

		return null;
	}

	/**
	 * Get Bearer token
	 */
	public static function getBearerToken()
	{
		$token = Authentication::getBearerToken();
		if ($token) return $token;

		static::$errorsArray = array_merge(static::$errorsArray, Authentication::errors());

		return null;
	}

	/**
	 * Get the current user data from token
	 *
	 * @param string $table The table to look for user
	 * @param array $hidden Fields to hide from user array
	 */
	public static function user($table = "users", $hidden = [])
	{
		if (!static::id()) {
			if (static::config("USE_SESSION")) {
				return static::$session->get("AUTH_USER");
			}

			return null;
		}

		$user = static::$db->select($table)->where(static::$settings["ID_KEY"], static::id())->fetchAssoc();

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
	public static function id()
	{
		if (static::config("USE_SESSION")) {
			return static::$session->get("AUTH_USER")[static::$settings["ID_KEY"]] ?? null;
		}

		$payload = static::validateToken(static::getSecretKey());
		if (!$payload) return null;
		return $payload->user_id;
	}

	/**
	 * Return form field
	 */
	public static function get($param)
	{
		return static::$form->get($param);
	}

	/**
	 * Get all authentication errors as associative array
	 */
	public static function errors(): array
	{
		return static::$errorsArray;
	}
}
