<?php
	namespace Leaf;

	use \Leaf\Helpers\JWT;

	class Authentication extends JWT {
		protected $errorsArray = [];
		
		public function generateSimpleToken($user_id, $secret_phrase) {
			define('SECRET_KEY', $secret_phrase);
			$payload = array(
				'iat' => time(),
				'iss' => 'localhost',
				'exp' => time() + (15*60),
				'userId' => $user_id
			);

			return $this->encode($payload, $secret_phrase);
		}

		public function generateToken($payload, $secret_phrase) {
			define('SECRET_KEY', $secret_phrase);

			return $this->encode($payload, $secret_phrase);
		}

		/**
		 * Get Authorization Headers
		 */
		public function getAuthorizationHeader(){
			$headers = null;
			
	        if (isset($_SERVER['Authorization'])) {
	            $headers = trim($_SERVER["Authorization"]);
	        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
	            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
	        } else if (function_exists('apache_request_headers')) {
	            $requestHeaders = apache_request_headers();
	            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
				$requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
				
	            if (isset($requestHeaders['Authorization'])) {
	                $headers = trim($requestHeaders['Authorization']);
	            }
	        }
	        return $headers;
		}

		/**
	     * get access token from header
	     * */
	    public function getBearerToken() {
			$headers = $this->getAuthorizationHeader();
			
	        if (!empty($headers)) {
	            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
	                return $matches[1];
				}
				$this->errorsArray["token"] = "Access token not found";
				return false;
			}

			$this->errorsArray["token"] = "Access token not found";
			return false;
		}
		
		public function validateToken() {
			$bearerToken = $this->token->getBearerToken();

			if ($bearerToken == false) {
				return false;
			}
			
			try {
				$payload = $this->token->decode($bearerToken, JWT_KEY, ['HS256']);
				return $payload;
			} catch(\Throwable $err) {
				$this->errorsArray["error"] = $err;
				return false;
			}
		}

		public function validate($token) {
			try {
				$payload = $this->token->decode($token, JWT_KEY, ['HS256']);
				return $payload;
			} catch(\Throwable $err) {
				$this->errorsArray["error"] = $err;
				return false;
			}
		}

		/**
		 * Get all authentication errors as associative array
		 */
		public function errors() {
			return $this->errorsArray;
		}
	}
