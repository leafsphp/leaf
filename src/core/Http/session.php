<?php
  	namespace Leaf\Core\Http;
  
	  /**
	 *  Leaf Session
	 *  --------
	 *  Session management made simple
	 */
    class Session {
        public function __construct() {
			!isset($_SESSION) ? session_start() : null;
			if (!isset($_SESSION['id'])) {
				$this->set("id", session_id());
			}
		}
		
		/**
		 * Get a session variable
		 *
		 * @param string $param: The session variable to get
		 *
		 * @return string, string: session variable
		 */
        public function get($param) {
			if (isset($_SESSION)) {
				return $_SESSION[$param];
			} else {
				return null;
			}
		}
		
		/**
		 * Get all session variables as an array
		 *
		 * @return array, array of session variables
		 */
        public function getBody() {
			if (isset($_SESSION)) {
				$body = array();
				foreach($_SESSION as $key => $value) {
					$body[$key] = $value;
				}
				return count($body) > 0 ? $body : null;
			} else {
				return null;
			}
		}
		
		/**
		 * Set a new session variable
		 *
		 * @param string $key: The session variable key
		 * @param string $value: The session variable value
		 *
		 * @return void
		 */
		public function set($key, $value) {
			if (!isset($_SESSION)) {
				session_start();
			}
			$_SESSION[$key] = $value;
		}

		/**
		 * Remove a session variable
		 *
		 * @param string $key: The session variable key
		 *
		 * @return void
		 */
		public function remove($key) {
			if (!isset($_SESSION)) {
				echo "There's no active session";
				exit();
			}
			unset($_SESSION[$key]);
		}
		
		/**
		 * End the current session
		 *
		 * @return void
		 */
		public function destroy() {
			if (isset($_SESSION)) {
				session_destroy();
			} else {
				echo "No active session";
				exit();
			}
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
				session_start();
			}
			session_reset();
			if ($id == null) {
				$id = session_id();
			}
			$this->set("id", session_id($id));
		}

		/**
		 * Get the current session id: will set the session id if none is found
		 *
		 * @param string [optional] $id: id to override the default
		 *
		 * @return string: session id
		 */
		public function id($id = null) {
			if ($id == null) {
				$id = session_id();
			}
			if (!isset($_SESSION['id'])) {
				$this->set("id", session_id($id));
			}
			if ($id != null) {
				$this->regenrate();
			}
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

		// to add in later versions
		// session_set_cookie_params(3600, "/", null, true);
		// \session_unset();
		// session_encode();
		// session_decode();
    };
