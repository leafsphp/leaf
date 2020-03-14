<?php
	namespace Leaf\Http;
  
	  /**
	 *  Leaf Session
	 *  --------
	 *  Session management made simple
	 */
    class Session {
		public $response;

        public function __construct() {
			$this->response = new \Leaf\Http\Response;
			!isset($_SESSION) ? session_start() : null;
			if (!isset($_SESSION['id'])) {
				$this->id();
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
			if (isset($_SESSION[$param])) {
				return $_SESSION[$param];
			} else {
				$this->response->throwErr("$param not found in session, initialise it or check your spelling");
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
		public function set($key, $value = null) {
			if (!isset($_SESSION)) {
				session_start();
			}
			if (is_array($key)) {
				foreach ($key as $name => $val) {
					$_SESSION[$name] = htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
				}
			} else {
				if (is_array($value)) {
					$_SESSION[$key] = [];

					foreach ($value as $name => $var) {
						$_SESSION[$key][$name] = htmlspecialchars($var, ENT_QUOTES, 'UTF-8');
					}
				} else {
					$_SESSION[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
				}
			}
		}

		protected function unset_session_var($key) {
			unset($_SESSION[$key]);
		}

		/**
		 * Remove a session variable
		 *
		 * @param string $key: The session variable key
		 *
		 * @return void
		 */
		public function unset($key) {
			if (!isset($_SESSION)) {
				$this->response->throwErr("There's no active session");
				exit();
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
			if (isset($_SESSION)) {
				session_destroy();
			} else {
				$this->response->throwErr("There's no active session");
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
		// session_encode();
		// session_decode();
    };
