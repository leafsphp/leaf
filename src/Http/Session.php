<?php
namespace Leaf\Http;

/**
 *  Leaf Session
 *  ----------------
 *  App session management made simple with Leaf 
 * 
 * @author Michael Darko
 * @since 1.5.0
 */
class Session {
	protected $errorsArray = [];

	public function __construct($start = true) {
		if ($start == true) session_start(); 
	}
	
	/**
	 * Get a session variable
	 *
	 * @param string $param: The session variable to get
	 *
	 * @return string, string: session variable
	 */
	public function get($param) {
		if (isset($_SESSION[$param])) return $_SESSION[$param];
		
		$this->errorsArray[$param] = "$param not found in session, initialise it or check your spelling";
		return false;
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
	public function retrieve($key, $defaultValue = null)
	{
		if (!isset($_SESSION[$key])) return $defaultValue;

		$value = $this->get($key);
		$this->unset_session_var($key);

		return $value;
	}
	
	/**
	 * Get all session variables as an array
	 *
	 * @return array|null array of session variables
	 */
	public function body() {
		if (!isset($_SESSION)) {
			$this->errorsArray["session"] = "No active session found!";
			return false;
		}

		$body = array();
		foreach($_SESSION as $key => $value) {
			$body[$key] = $value;
		}
		return $body;
	}
	
	/**
	 * Set a new session variable
	 *
	 * @param string $key: The session variable key
	 * @param string $value: The session variable value
	 *
	 * @return void
	 */
	public function set($key, $value = null, $sanitize = true) {
		if (is_array($key)) {
			foreach ($key as $name => $val) {
				$this->set($name, $val);
			}
		} else {
			$_SESSION[$key] = $value;
			if ($sanitize) $_SESSION = \Leaf\Util::sanitize($_SESSION);
		}
	}

	/**
	 * Remove a session variable
	 */
	protected function unset_session_var($key) {
		unset($_SESSION[$key]);
	}

	/**
	 * Remove a session variable
	 *
	 * @param string $key: The session variable key
	 *
	 * @return void|false
	 */
	public function unset($key) {
		if (!isset($_SESSION)) {
			$this->errorsArray["session"] = "No active session found!";
			return false;
		}
		
		if (is_array($key)) {
			foreach ($key as $field) {
				$this->unset_session_var($field);
			}
		} else {
			$this->unset_session_var($key);
		}
	}
	
	/**
	 * End the current session
	 *
	 * @return void
	 */
	public function destroy() {
		if (!isset($_SESSION)) {
			$this->errorsArray["session"] = "No active session found!";
			return false;
		}
		session_destroy();
	}

	/**
	 * Reset the current session
	 * 
	 * @param string $id: id to override the default
	 * 
	 * @return void
	 */
	public function reset($id = null) {
		if (!isset($_SESSION)) {
			$this->errorsArray["session"] = "No active session found!";
			return false;
		}
		session_reset();
		$this->set("id", $id ?? session_id());
	}

	/**
	 * Get the current session id: will set the session id if none is found
	 *
	 * @param string [optional] $id: id to override the default
	 *
	 * @return string: session id
	 */
	public function id($id = null) {
		if (!isset($_SESSION['id'])) $this->set("id", $id ?? session_id());
		return $this->get("id");
	}

	/**
	 * Regenerate the session id
	 * 
	 * @param bool $clearData: Clear all session data?
	 * 
	 * @return void
	 */
	public function regenerate($clearData = false) {
		$this->set("id", session_regenerate_id($clearData));
	}

	/**
	 * Encodes the current session data as a string
	 */
	public function encode() : string
	{
		return session_encode();
	}

	/**
	 * Decodes session data from a string
	 */
	public function decode($data)
	{
		return session_decode($data);
	}

	/**
	 * Return errors if any
	 * 
	 * @return array
	 */
	public function errors() : array
	{
		return $this->errorsArray;
	}
};
