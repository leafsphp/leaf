<?php
namespace Leaf;

use \Leaf\Http\Request;

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
			$this->errorsArray[$key] = $message;
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
			$this->errorsArray[$key] = $message;
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
		$supportedRules = ["required", "number", "text", "textonly", "validusername", "email", "nospaces"];

		$fields = [];
		
		foreach ($rules as $param => $rule) {
			array_push($fields, ["name" => $param, "value" => $this->get($param), "rule" => $rule ]);
		}
		
		foreach ($fields as $field) {
			if (is_array($field["rule"])) {
				foreach ($field["rule"] as $rule) {
					$rule = strtolower($rule);

					if (!in_array($rule, $supportedRules)) {
						echo $rule." is not a supported rule<br>";
						echo "Supported rules are ".json_encode($supportedRules);
						exit();
					}
					$this->validateField($field["name"], $field["value"], $rule);
				}
			} else {
				$field["rule"] = strtolower($field["rule"]);

				if (!in_array($field["rule"], $supportedRules)) {
					echo $field["rule"]." is not a supported rule<br>";
					echo "Supported rules are ".json_encode($supportedRules);
					exit();
				}
				$this->validateField($field["name"], $field["value"], $field["rule"]);
			}
		}
	}

	public function validateField($fieldName, $fieldValue, $rule) {
		if ($rule == "required" && ($fieldValue == "" || $fieldValue == null)) {
			$this->errorsArray[$fieldName] = $fieldName." is required";
		}

		if ($rule == "number" && ($fieldValue == "" || $fieldValue == null || !preg_match('/^[0-9]+$/', $fieldValue))) {
			$this->errorsArray[$fieldName] = $fieldName." must only contain numbers";
		}

		if ($rule == "text" && ($fieldValue == "" || $fieldValue == null || !preg_match('/^[_a-zA-Z ]+$/', $fieldValue))) {
			$this->errorsArray[$fieldName] = $fieldName." must only contain text and spaces";
		}
		
		if ($rule == "textonly" && ($fieldValue == "" || $fieldValue == null || !preg_match('/^[_a-zA-Z]+$/', $fieldValue))) {
			$this->errorsArray[$fieldName] = $fieldName." must only contain text";
		}

		if ($rule == "validusername" && ($fieldValue == "" || $fieldValue == null || !preg_match('/^[_a-zA-Z0-9]+$/', $fieldValue))) {
			$this->errorsArray[$fieldName] = $fieldName." must only contain characters 0-9, A-Z and _";
		}

		if ($rule == "email" && ($fieldValue == "" || $fieldValue == null || !!filter_var($fieldValue, 274) == false)) {
			$this->errorsArray[$fieldName] = $fieldName." must be a valid email";
		}

		if ($rule == "nospaces" && ($fieldValue == "" || $fieldValue == null || !preg_match('/^[ ]+$/', $fieldValue))) {
			$this->errorsArray[$fieldName] = $fieldName." can't contain any spaces";
		}
	}

	/**
	 * Directly "submit" a form without having to work with any mark-up
	 */
	public function submit(string $method, string $action, array $fields) {
		$form_fields = "";

		foreach ($fields as $key => $value) {
			$form_fields = $form_fields."<input type=\"hidden\" name=\"$key\" value=".htmlspecialchars($value, ENT_QUOTES, 'UTF-8').">";
		}

		echo "
			<form action=\"$action\" method=\"$method\" id=\"leaf_submit_form\">$form_fields</form>
			<script>document.getElementById(\"leaf_submit_form\").submit();</script>
		";
	}

	public function isEmail($value) {
		return !!filter_var($value, 274);
	}

	/**
     * Return the form fields+data
     *
     * @return string
     */
	public function returnFields() {
		return $this->body();
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
