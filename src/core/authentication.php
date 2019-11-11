<?php
	namespace Leaf\Core;
	use \Leaf\Helpers\JWT;

	class Authentication extends JWT {
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

		public function validateToken() {
			try {
				$token = $this->getBearerToken();
				$payload = $this->decode($token, SECRET_KEY, ['HS256']);

				// $stmt = $this->dbConn->prepare("SELECT * FROM users WHERE id = :userId");
				// $stmt->bindParam(":userId", $payload->userId);
				// $stmt->execute();
				// $user = $stmt->fetch(PDO::FETCH_ASSOC);
				// if(!is_array($user)) {
				// 	$this->returnResponse(INVALID_USER_PASS, "This user is not found in our database.");
				// }
				// $this->userId = $payload->userId;
			} catch (Exception $e) {
				// $this->throwError($e->getMessage(), ACCESS_TOKEN_ERRORS);
			}
		}

		/**
	     * get access token from header
	     * */
	    public function getBearerToken() {
	        $headers = $this->getAuthorizationHeader();
	        // HEADER: Get the access token from the header
	        if (!empty($headers)) {
	            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
	                return $matches[1];
	            }
	        }
	        // $this->throwError(ATHORIZATION_HEADER_NOT_FOUND, 'Access Token Not found');
		}

		public function getAuthorizationHeader(){
	        $headers = null;
	        if (isset($_SERVER['Authorization'])) {
	            $headers = trim($_SERVER["Authorization"]);
	        }
	        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
	            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
	        } elseif (function_exists('apache_request_headers')) {
	            $requestHeaders = apache_request_headers();
	            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
	            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
	            if (isset($requestHeaders['Authorization'])) {
	                $headers = trim($requestHeaders['Authorization']);
	            }
	        }
	        return $headers;
	    }
	}
