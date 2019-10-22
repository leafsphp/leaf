<?php
  	namespace Leaf\Core\Http;
  
    class Session {
        public function __construct() {
			!isset($_SESSION) ? session_start() : null;
		}
		
        public function getParam($param) {
			if (isset($this->$_SESSION)) {
				return $this->$_SESSION[$param];
			} else {
				return null;
			}
        }
		
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
		
		public function set($key, $value) {
			if (!isset($_SESSION)) {
				session_start();
			}
			$_SESSION[$key] = $value;
		}
		
		public function destroy() {
			if (isset($_SESSION)) {
				session_destroy();
			} else {
				echo "No active session";
			}
		}
    };
