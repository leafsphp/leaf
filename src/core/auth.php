<?php
	class Authentication extends JWT {
		public function generateToken($user_id, $secret_phrase) {
			$payload = array(
				'iat' => time(),
				'iss' => 'localhost',
				'exp' => time() + (15*60),
				'userId' => $user['id']
			);
	
			return $this->encode($payload, $secret_phrase);
		}
	}