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
				$this->set("id", session_id($id));
			}
		}
		
		/**
		 * Get a session variable
		 *
		 * @param string $param: The session variable to get
		 *
		 * @return string, string: session variable
		 */
        public function getParam($param) {
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
			if (!isset($_SESSION['id'])) {
				$this->set("id", session_id($id));
			}
			return $this->getParam("id");
		}

		/**
		 * Regenerate the session id
		 * 
		 * @param bool $clearData: Clear all session data?
		 * 
		 * @return void
		 */
		public function regenerate($clearData = null) {
			$this->set("id", session_regenerate_id($clearData));
		}

		// to add in later versions
		// session_set_cookie_params(3600, "/", null, true);
		// \session_unset();
		// session_encode();
		// session_decode();
    };
