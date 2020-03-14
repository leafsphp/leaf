<?php
namespace Leaf;

use \Leaf\Db\Mysqli;
use \Leaf\Form;
use \Leaf\Http\Response;
use \Leaf\Authentication;

/**
 * Leaf Simple Auth
 * ---------------
 * Perform simple authentication tasks.
 */
class Auth extends Mysqli {
	protected $errorsArray = [];

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
	 * @param string table: Table to look for users
	 * @param string condition: Conditions to be met for login
	 * @param string password_encode: Password encode type, should match password
	 * 
	 * @return array user: all user info + tokens + session data
	 */
	public function login(string $table, array $credentials, string $password_encode = null) {
		if ($password_encode == "md5" && isset($credentials["password"])) {
			$credentials["password"] = md5($credentials["password"]);
		}

		$keys = [];
		$data = [];

		foreach ($credentials as $key => $value) {
			// try {
			// 	!$this->select($table, "*", "$key = ?", [$value]);
			// } catch (\Throwable $th) {
			// 	$this->response->throwErr(["error" => "$key is not a valid column in the $table table"]);
			// 	exit();
			// }

			array_push($keys, $key);
			array_push($data, $value);

			if ($key == "email") $this->form->validate(["email" => "email"]); 
			else if ($key == "username") $this->form->validate(["username" => "validusername"]); 
			else $this->form->validate([$key => "required"]);
		}

		$keys_length = count($keys);
		$data_length = count($data);

		if (!empty($this->form->errors())) {
			foreach ($this->form->errors() as $key => $value) {
				$this->errorsArray[$key] = $value;
			}
			return false;
        } else {
			$condition = "";

			for ($i=0; $i < $keys_length; $i++) { 
				$condition = $condition.$keys[$i]." = ?";
				if ($i < $keys_length - 1) {
					$condition = $condition." AND ";
				}
			}

			$user = $this->select($table, "*", $condition, $data)->fetchObj();

			if (!$user) {
				$this->errorsArray["auth"] = "Incorrect credentials, please check and try again";
				return false;
			}

			$token = $this->token->generateSimpleToken($user->id, "User secret key");

			if ($token == false) {
				foreach ($this->token->errors() as $key => $value) {
					$this->errorsArray[$key] = $value;
				}
				return false;
			}

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
	public function register(string $table, array $credentials, array $uniques = null, string $password_encode = null) {
		if ($password_encode == "md5" && isset($credentials["password"])) {
			$credentials["password"] = md5($credentials["password"]);
		}

		$keys = [];
		$data = [];

		foreach ($credentials as $key => $value) {
			// try {
			// 	$this->select($table, "*", "$key = ?", [$value]);
			// } catch (\Throwable $th) {
			// 	$this->response->throwErr(["error" => "$key is not a valid column in the $table table"]);
			// 	exit();
			// }

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
			array_push($this->errorsArray, $this->form->errors());
			return false;
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

			try {
				$this->insert($table, $table_names, $table_values, $data);
			} catch (\Throwable $th) {
				$this->errorsArray["error"] = $th;
				return false;
			}
		}
	}

	/**
	 * Validate Json Web Token
	 */
	public function validate($token) {
		$payload = $this->token->validate($token);

		if ($payload == false) {
			foreach ($this->token->errors() as $key => $value) {
				$this->errorsArray[$key] = $value;
			}
			return false;
		}

		return $payload;
	}

	/**
	 * Validate Bearer Token
	 */
	public function validateToken() {
		$payload = $this->token->validateToken();

		if ($payload == false) {
			foreach ($this->token->errors() as $key => $value) {
				$this->errorsArray[$key] = $value;
			}
			return false;
		}

		return $payload;
	}

	/**
	 * Get Bearer token
	 */
	public function getBearerToken() {
		$token = $this->token->getBearerToken();

		if ($token == false) {
			foreach ($this->token->errors() as $key => $value) {
				$this->errorsArray[$key] = $value;
			}
			return false;
		}

		return $token;
	}

	/**
	 * Return form field
	 */
	public function get($param) {
		return $this->form->get($param);
	}

	/**
	 * Get all authentication errors as associative array
	 */
	public function errors() {
		return $this->errorsArray;
	}
}