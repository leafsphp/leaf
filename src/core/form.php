<?php
namespace Leaf\Core;

use Leaf\Core\Http\Request;

/**
 *  Leaf Forms
 *  --------
 *  Simple Form Validation with Leaf
 */
class Form extends Request {
	public $errorsArray = array();
	/**
     * make sure that the form data is safe to work with
     *
     * @param string $data: The data gotten from the form field
     *
     * @return string, string: The parsed data
     */
	public function sanitizeInput($data) {
		// check for sql injection possibilities
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	}

	/**
     * Check if param is empty
     *
     * @param string $data: The data gotten from the form field
     * @param string $key: The name of the form field
     * @param string $message: The message to add to the errors array
     *
     * @return void
     */
	public function isEmpty($data, $key, $message="This field is required") {
		if (empty($data)) {
			$this->errors[$key] = $message;
		}
		return;
	}

	/**
     * Check if param is null
     *
     * @param string $data: The data gotten from the form field
     * @param string $key: The name of the form field
     * @param string $message: The message to add to the errors array
     *
     * @return void
     */
	public function isNull($data, $key, $message="This field cannot be null") {
		if (is_null($data)) {
			$this->errors[$key] = $message;
		}
		return;
	}

	/**
	 * Validate the given request with the given rules.
	 * 
     * @param  array  $rules
     * @param  array  $messages
	 * 
     * @return void
	 */
	public function validate(array $rules, array $messages = []) {
		$supportedRules = ["required", "number", "textonly", "validusername", "email", "nospaces"];

		$fields = [];
		
		foreach ($rules as $param => $rule) {
			array_push($fields, ["name" => $param, "value" => $this->get($param), "rule" => strtolower($rule)]);
		}
		
		foreach ($fields as $field) {
			if (!in_array($field["rule"], $supportedRules)) {
				echo $field["rule"]." is not a supported rule<br>";
				echo "Supported rules are ".json_encode($supportedRules);
				exit();
			}

			if ($field["rule"] == "required" && ($field["value"] == "" || $field["value"] == null)) {
				$this->errors[$field["name"]] = $field["name"]." is required";
			}

			if ($field["rule"] == "number" && ($field["value"] == "" || $field["value"] == null || !preg_match('/^[0-9]+$/', $field["value"]))) {
				$this->errors[$field["name"]] = $field["name"]." must only contain numbers";
			}

			if ($field["rule"] == "textonly" && ($field["value"] == "" || $field["value"] == null || !preg_match('/^[_a-zA-Z]+$/', $field["value"]))) {
				$this->errors[$field["name"]] = $field["name"]." must only contain text";
			}

			if ($field["rule"] == "validusername" && ($field["value"] == "" || $field["value"] == null || !preg_match('/^[_a-zA-Z0-9]+$/', $field["value"]))) {
				$this->errors[$field["name"]] = $field["name"]." must only contain characters 0-9, A-Z and _";
			}

			if ($field["rule"] == "email" && ($field["value"] == "" || $field["value"] == null || !!filter_var($field["value"], 274) == false)) {
				$this->errors[$field["name"]] = $field["name"]." must be a valid email";
			}

			if ($field["rule"] == "nospaces" && ($field["value"] == "" || $field["value"] == null || !preg_match('/^[ ]+$/', $field["value"]))) {
				$this->errors[$field["name"]] = $field["name"]." can't contain any spaces";
			}
		}
	}

	/**
     * Return the form fields+data
     *
     * @return string
     */
	public function returnFields() {
		return $this->getBody();
	}

	/**
     * Return the form errors
     *
     * @return string
     */
	public function errors() {
		return $this->errorsArray;
	}
}
