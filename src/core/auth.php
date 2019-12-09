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
	public function basicLogin(string $username, string $password, string $password_encode = "md5") {
		$this->form->validate([
			"username" => "required",
			"password" => "required"
		]);
		if (!empty($this->form->returnErrors())) {
            $this->response->respond([
                "errors" => $this->form->returnErrors()
            ]);
        } else {
			if ($password_encode == "md5") {
				$password = md5($password);
			} else {
				$password = \base64_encode($password);
			}
			$user = $this->select("users", "*", "username = ? AND password = ?", [$username, $password])->fetchObj();
			$token = $this->token->generateSimpleToken($user->id, "User secret key");
			$user->token = $token;
			unset($user->password);

			return $user;
        }
	}

	public function basicRegister(string $username, string $email, string $password, string $confirm_password, string $password_encode = "md5") {
		$this->form->validate([
			"username" => "required",
			"email" => "required",
			"password" => "required",
			"confirm_password" => "required"
		]);
		if ($password != $confirm_password) {
			$this->form->errors["password"] = "Your passwords don't match";
		}
		if (!empty($this->form->returnErrors())) {
            $this->response->respond([
                "errors" => $this->form->returnErrors()
            ]);
        } else {
			if ($password_encode == "md5") {
				$password = md5($password);
			} else {
				$password = \base64_encode($password);
			}
            $this->insert("users", "username, email, password", "?, ?, ?", [$username, $email, $password]);
        }
	}

	public function get($param) {
		return $this->form->get($param);
	}
}