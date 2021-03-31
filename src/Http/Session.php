<?php

namespace Leaf\Http;

/**
 * Leaf Session
 * ----------------
 * App session management made simple with Leaf 
 * 
 * @author Michael Darko
 * @since 1.5.0
 */
class Session
{
	protected static $errorsArray = [];

	public function __construct($start = true)
	{
		if ($start == true) session_start();
	}

	/**
	 * Get a session variable
	 *
	 * @param string|array $param: The session variable to get
	 *
	 * @return mixed
	 */
	public static function get($param, bool $sanitize = true)
	{
		if (is_array($param)) {
			$fields = [];

			foreach ($param as $item) {
				$fields[$item] = static::get($item, $sanitize);
			}

			return $fields;
		}

		if (!isset($_SESSION[$param])) {
			return null;
		}

		$data = $_SESSION[$param];

		if ($sanitize) {
			$data = \Leaf\Util::sanitize($data);
		}

		return $data;
	}

	/**
	 * Returns the requested value and removes it from the session
	 *
	 * This is identical to calling `get` first and then `unset` for the same key
	 *
	 * @param string $key the key to retrieve and remove the value for
	 * @param mixed $defaultValue the default value to return if the requested value cannot be found
	 * 
	 * @return mixed the requested value or the default value
	 */
	public static function retrieve($key, $defaultValue = null)
	{
		if (!isset($_SESSION[$key])) return $defaultValue;

		$value = static::get($key);
		static::unset_session_var($key);

		return $value;
	}

	/**
	 * Get all session variables as an array
	 *
	 * @return array|null array of session variables
	 */
	public static function body()
	{
		if (!isset($_SESSION)) {
			static::$errorsArray["session"] = "No active session found!";
			return false;
		}

		$body = [];
		foreach ($_SESSION as $key => $value) {
			$body[$key] = $value;
		}
		return $body;
	}

	/**
	 * Set a new session variable
	 *
	 * @param string|array $key: The session variable key
	 * @param string $value: The session variable value
	 *
	 * @return void
	 */
	public static function set($key, $value = null)
	{
		if (is_array($key)) {
			foreach ($key as $name => $val) {
				static::set($name, $val);
			}
		} else {
			$_SESSION[$key] = $value;
		}
	}

	/**
	 * Remove a session variable
	 */
	protected static function unset_session_var($key)
	{
		unset($_SESSION[$key]);
	}

	/**
	 * Remove a session variable
	 *
	 * @param string $key: The session variable key
	 *
	 * @return void|false
	 */
	public static function unset($key)
	{
		if (!isset($_SESSION)) {
			static::$errorsArray["session"] = "No active session found!";
			return false;
		}

		if (is_array($key)) {
			foreach ($key as $field) {
				static::unset_session_var($field);
			}
		} else {
			static::unset_session_var($key);
		}
	}

	/**
	 * End the current session
	 *
	 * @return void
	 */
	public static function destroy()
	{
		if (!isset($_SESSION)) {
			static::$errorsArray["session"] = "No active session found!";
			return false;
		}
		session_destroy();
	}

	/**
	 * Reset the current session
	 * 
	 * @param string $id: id to override the default
	 * 
	 * @return false|void
	 */
	public static function reset($id = null)
	{
		if (!isset($_SESSION)) {
			static::$errorsArray["session"] = "No active session found!";
			return false;
		}

		session_reset();
		static::set("id", $id ?? session_id());
	}

	/**
	 * Get the current session id: will set the session id if none is found
	 *
	 * @param string [optional] $id: id to override the default
	 *
	 * @return string
	 */
	public static function id($id = null)
	{
		if (!isset($_SESSION['id'])) static::set("id", $id ?? session_id());
		return static::get("id");
	}

	/**
	 * Regenerate the session id
	 * 
	 * @param bool $clearData: Clear all session data?
	 * 
	 * @return bool True on success, false on failure
	 */
	public static function regenerate($clearData = false)
	{
		return session_regenerate_id($clearData);
	}

	/**
	 * Encodes the current session data as a string
	 * 
	 * @return string
	 */
	public static function encode(): string
	{
		return session_encode();
	}

	/**
	 * Decodes session data from a string
	 * 
	 * @return bool
	 */
	public static function decode($data)
	{
		return session_decode($data);
	}

	/**
	 * Return errors if any
	 * 
	 * @return array
	 */
	public static function errors(): array
	{
		return static::$errorsArray;
	}

	// -------------- flash messages ---------------
	/**
	 * Set or get a flash message
	 * 
	 * @param string|null $message The flash message
	 */
	public static function flash($message = null)
	{
		if (!$message) {
			return \Leaf\Flash::display();
		}

		\Leaf\Flash::set($message);
	}
};
