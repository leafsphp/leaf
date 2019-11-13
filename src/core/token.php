<?php
namespace Leaf\Core;

use Leaf\Core\Http\Response;

// Token objects from this class do not implement bin2hex, rather, base64 is used

/**
 *  Leaf Tokens
 *  --------
 *  This is just a simple way to create tokens.Use this if you prefer not to use JWT
 */
class Token {
	protected $token;

	/**
     * generate a simple user token
     *
     * @param string $username: The username of the user
     * @param integer $user_id: The id of the user
     * @param integer $expiry_time: When the token should expire from now. In seconds.
     *
     * @return string, string: token
     */
	public function generateSimpleToken($username, $user_id, $expiry_time = (7 * 24 * 60 * 60)) {
		$payload = array(
			"id" => $user_id,
			"username" => $username,
			"expiry_time" => time() + $expiry_time,
			"secret_phrase" => "@Leaf1sGr8"
		);
		return $this->createToken($payload);
	}

	/**
     * generate a simple user token
     *
     * @param array $token_data: All data to be encoded
     * @param integer $expiry_time: When the token should expire from now. In seconds.
     *
     * @return string, string: token
     */
	public function generateToken($token_data, $expiry_time = (7 * 24 * 60 * 60)) {
		$payload = array();
		foreach ($token_data as $key => $value) {
			$payload[$key] = $value;
		}
		$payload["expiry_time"] =  time() + $expiry_time;
		$payload["secret_phrase"] = "@Leaf1sGr8";
		
		return $this->createToken($payload);
	}

	private function createToken($token_data) {
		$token_data = json_encode($token_data);
		$token = base64_encode($token_data);
		return $token;
	}

	/**
     * validate a user token
     *
     * @param string $token: The actual token returned from user
     *
     * @return string, string: token
     */
	public function validateToken($token) {
		// check if the app secret is @Leaf1sGr8
		$token = base64_decode($token);
		$token = json_decode($token);
		if ($token['secret_phrase' != "@Leaf1sGr8"] || !isset($token['secret_phrase'])) {
			$this->response->throwErr(array(
				'error' => 'token is invalid'
			));
		}
		if ($token['expiry_time'] <= time() || !isset($token['expiry_time'])) {
			$this->response->throwErr(array(
				'error' => 'token has expired or is invalid'
			));
		}
		return $token;
	}

	// Example token => eyJpZDogMSwgInVzZXJuYW1lIjogIk15Y2hpIiwgImV4cGlyeV90aW1lIjogIjI3LzIwLzIwIiwgICJzZWNyZXRfcGhyYXNlIjogIkBMZWFmMXNHcjgifQ==
}
