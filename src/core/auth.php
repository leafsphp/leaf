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
			foreach ($credentials as $key => $value) {
				try {
					!$this->select($table, "*", "$key = ?", [$value]);
				} catch (\Throwable $th) {
					$this->response->throwErr(["error" => "$key is not a valid column in the $table table"]);
					exit();
				}
			}

			$condition = "";

			for ($i=0; $i < $keys_length; $i++) { 
				$condition = $condition.$keys[$i]." = ?";
				if ($i < $keys_length - 1) {
					$condition = $condition." AND ";
				}
			}

			try {
				$user = $this->select($table, "*", $condition, $data)->fetchObj();
			} catch (\Throwable $th) {
				$this->response->throwErr(["error" => "You supplied an invalid selector to \$auth->login, correct and try again"]);
			}

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

	/**
	 * Simple user registration
	 * 
	 * @param string table: Table to store user in
	 * @param string condition: Conditions to be met for login
	 * @param string password_encode: Password encode type, should match password
	 * 
	 * @return array user: all user info + tokens + session data
	 */
	public function register($table, $credentials, $uniques = null, $password_encode = null) {
		if ($password_encode == "md5" && isset($credentials["password"])) {
			$credentials["password"] = md5($credentials["password"]);
		}

		$keys = [];
		$data = [];

		foreach ($credentials as $key => $value) {
			try {
				!$this->select($table, "*", "$key = ?", [$value]);
			} catch (\Throwable $th) {
				$this->response->throwErr(["error" => "$key is not a valid column in the $table table"]);
				exit();
			}

			array_push($keys, $key);
			array_push($data, $value);

			if ($key == "email") $this->form->validate(["email" => "email"]); 
			else if ($key == "username") $this->form->validate(["username" => "validusername"]); 
			else $this->form->validate([$key => "required"]);
		}

		$keys_length = count($keys);
		$data_length = count($data);

		if ($uniques != null) {
			foreach ($uniques as $unique) {
				if (!isset($credentials[$unique])) {
					$this->response->respond(["error" => "$unique not found, Add $unique to your \$auth->register credentials or check your spelling."]);
					exit();
				} else {
					if ($this->select($table, "*", "$unique = ?", [$credentials[$unique]])->fetchObj()) {
						$this->form->errorsArray[$unique] = "$unique already exists";
					}
				}
			}
		}

		if (!empty($this->form->errors())) {
            $this->response->throwErr($this->form->errors());
			exit();
        } else {
			$table_names = "";
			$table_values = "";

			for ($i=0; $i < $keys_length; $i++) { 
				$table_names = $table_names.$keys[$i];
				if ($i < $keys_length - 1) {
					$table_names = $table_names.", ";
				}

				$table_values = $table_values."?";
				if ($i < $keys_length - 1) {
					$table_values = $table_values.", ";
				}
			}

			$this->insert($table, $table_names, $table_values, $data);
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