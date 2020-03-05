<?php
namespace Leaf\Core;

use Leaf\Core\Db\Mysqli;
use Leaf\Core\Form;
use Leaf\Core\Http\Response;
use Leaf\Core\Authentication;

/**
 * Leaf Simple Auth
 * ---------------
 * Perform simple authentication tasks.
 */
class Auth extends Mysqli {
	public function __construct() {
		$this->form = new Form;
		$this->response = new Response;
		$this->token = new Authentication;
	}

	public function connect($host, $user, $password, $dbname) {
		Mysqli::connect($host, $user, $password, $dbname);
	}

	/**
	 * Simple user login
	 * 
	 * @param string username: Username
	 * @param string password: Password in md5
	 * @param string password_encode: Password encode type, should match password
	 * 
	 * @return array user: all user info + tokens + session data
	 */
	public function basicLogin($username, $password, $password_encode = "md5") {
		$this->form->validate([
			"username" => "validusername",
			"password" => "required"
		]);
		if (!$this->select("users", "*", "username = ?", [$username])->fetchObj()) {
			$this->form->errorsArray["username"] = "Username doesn't exist";
		}
		if (!empty($this->form->errors())) {
            $this->response->throwErr($this->form->errors());
			exit();
        } else {
			if ($password_encode == "md5") {
				$password = md5($password);
			} else {
				$password = \base64_encode($password);
			}
			$user = $this->select("users", "*", "username = ? AND password = ?", [$username, $password])->fetchObj();
			if (!$user) {
				$this->response->throwErr("Password is incorrect");
				exit();
			}
			$token = $this->token->generateSimpleToken($user->id, "User secret key");
			$user->token = $token;
			unset($user->password);

			return $user;
        }
	}

	/**
	 * Simple user login
	 * 
	 * @param string username: Username
	 * @param string password: Password in md5
	 * @param string password_encode: Password encode type, should match password
	 * 
	 * @return array user: all user info + tokens + session data
	 */
	public function emailLogin($email, $password, $password_encode = "md5") {
		if (!$this->select("users", "*", "email = ?", [$email])->fetchObj()) {
			$this->form->errorsArray["email"] = "Email doesn't exist";
		}
		$this->form->validate([
			"email" => "email",
			"password" => "required"
		]);
		if (!empty($this->form->errors())) {
            $this->response->throwErr($this->form->errors());
			exit();
        } else {
			if ($password_encode == "md5") {
				$password = md5($password);
			} else {
				$password = \base64_encode($password);
			}
			$user = $this->select("users", "*", "email = ? AND password = ?", [$email, $password])->fetchObj();
			if (!$user) {
				$this->response->throwErr("Password is incorrect");
				exit();
			}
			$token = $this->token->generateSimpleToken($user->id, "User secret key");
			$user->token = $token;
			unset($user->password);

			return $user;
        }
	}

	/**
	 * Simple user login
	 * 
	 * @param string condition: Conditions to be met for login
	 * @param string password_encode: Password encode type, should match password
	 * 
	 * @return array user: all user info + tokens + session data
	 */
	public function login($table, $credentials, $password_encode = null) {
		if ($password_encode == "md5" && isset($credentials["password"])) {
			$credentials["password"] = md5($credentials["password"]);
		}

		$keys = [];
		$data = [];

		foreach ($credentials as $key => $value) {
			array_push($keys, $key);
			array_push($data, $value);

			if ($key == "email") $this->form->validate(["email" => "email"]); 
			else if ($key == "username") $this->form->validate(["username" => "validusername"]); 
			else $this->form->validate([$key => "required"]);
		}

		$keys_length = count($keys);
		$data_length = count($data);

		if (!empty($this->form->errors())) {
            $this->response->throwErr($this->form->errors());
			exit();
        } else {
			$condition = "";

			for ($i=0; $i < $keys_length; $i++) { 
				$condition = $condition.$keys[$i]." = ?";
				if ($i < $keys_length - 1) {
					$condition = $condition." AND ";
				}
			}

			$user = $this->select("users", "*", $condition, $data)->fetchObj();

			if (!$user) {
				$this->response->throwErr("Incorrect credentials, please check and try again");
				exit();
			}
			$token = $this->token->generateSimpleToken($user->id, "User secret key");
			$user->token = $token;
			unset($user->password);

			return $user;
		}
	}

	public function basicRegister($username, $email, $password, $confirm_password, $password_encode = "md5") {
		$this->form->validate([
			"username" => "validUsername",
			"email" => "email",
			"password" => "required",
			"confirm_password" => "required"
		]);
		if ($this->select("users", "*", "username = ?", [$username])->fetchObj()) {
			$this->form->errorsArray["username"] = "Username already exists";
		}
		if ($this->select("users", "*", "email = ?", [$email])->fetchObj()) {
			$this->form->errorsArray["email"] = "Email is already registered";
		}
		if ($password != $confirm_password) {
			$this->form->errorsArray["password"] = "Your passwords don't match";
		}
		if (!empty($this->form->errors())) {
            $this->response->throwErr($this->form->errors());
			exit();
        } else {
			if ($password_encode == "md5") {
				$password = md5($password);
			} else {
				$password = \base64_encode($password);
			}
            $this->insert("users", "username, email, password", "?, ?, ?", [$username, $email, $password]);
        }
	}

	public function validateToken() {
		try {
			$bearerToken = $this->token->getBearerToken();
			$payload = $this->token->decode($bearerToken, JWT_KEY, ['HS256']);
			return $payload;
		} catch (Exception $e) {
			$this->response->respond([ "auth_error" => "Authentication failed. ".$e ]);;
			exit();
		}
	}

	public function get($param) {
		return $this->form->get($param);
	}
}