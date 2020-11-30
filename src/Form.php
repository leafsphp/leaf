<?php
namespace Leaf;

use Leaf\Http\Request;

/**
 *  Leaf Forms
 *  --------
 *  Simple Form Validation with Leaf
 */
class Form extends Request {
	/**
	 * Array holding all caught errors
	 */
	protected $errorsArray = [];

	/**
     * make sure that the form data is safe to work with
     *
     * @param string $data: The data gotten from the form field
     *
     * @return string, string: The parsed data
     */
	public function sanitizeInput($data) {
		return htmlspecialchars(stripslashes(trim($data)));
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
		$req = new \Leaf\Http\Request;
		
		foreach ($rules as $param => $rule) {
			array_push($fields, ["name" => $param, "value" => $req->get($param), "rule" => $rule ]);
		}
		
		foreach ($fields as $field) {
			if (is_array($field["rule"])) {
				foreach ($field["rule"] as $rule) {
					$rule = strtolower($rule);

					if (!in_array($rule, $supportedRules)) {
						echo $rule." is not a supported rule<br>";
						echo "Supported rules are ". json_encode($supportedRules);
						exit();
					}
					return $this->validateField($field["name"], $field["value"], $rule);
				}
			} else {
				$field["rule"] = strtolower($field["rule"]);

				if (!in_array($field["rule"], $supportedRules)) {
					echo $field["rule"]." is not a supported rule<br>";
					echo "Supported rules are ". json_encode($supportedRules);
					exit();
				}
				return $this->validateField($field["name"], $field["value"], $field["rule"]);
			}
		}
	}

	/**
	 * Validate data.
	 * 
	 * @param  array  $rules The data to be validated, plus rules
	 * @param  array  $messages
	 * 
	 * @return void
	 */
	public function validateData(array $rules, array $messages = [])
	{
		$supportedRules = ["required", "number", "text", "textonly", "validusername", "email", "nospaces"];

		$fields = [];

		foreach ($rules as $param => $rule) {
			array_push($fields, ["name" => $param, "value" => $param, "rule" => $rule]);
		}

		foreach ($fields as $field) {
			if (is_array($field["rule"])) {
				foreach ($field["rule"] as $rule) {
					$rule = strtolower($rule);

					if (!in_array($rule, $supportedRules)) {
						echo $rule . " is not a supported rule<br>";
						echo "Supported rules are " . json_encode($supportedRules);
						exit();
					}
					return $this->validateField($field["name"], $field["value"], $rule);
				}
			} else {
				$field["rule"] = strtolower($field["rule"]);

				if (!in_array($field["rule"], $supportedRules)) {
					echo $field["rule"] . " is not a supported rule<br>";
					echo "Supported rules are " . json_encode($supportedRules);
					exit();
				}
				return $this->validateField($field["name"], $field["value"], $field["rule"]);
			}
		}
	}

	/**
	 * Validate field data
	 * 
	 * @param string $fieldName The name of the field to validate
	 * @param string $fieldValue The value of the field to validate
	 * @param string $rule The rule to apply
	 */
	public function validateField($fieldName, $fieldValue, $rule) {
		$isValid = true;

		if ($rule == "required" && ($fieldValue == "" || $fieldValue == null)) {
			$this->errorsArray[$fieldName] = $fieldName." is required";
			$isValid = false;
		}

		if ($rule == "number" && ($fieldValue == "" || $fieldValue == null || !preg_match('/^[0-9]+$/', $fieldValue))) {
			$this->errorsArray[$fieldName] = $fieldName." must only contain numbers";
			$isValid = false;
		}

		if ($rule == "text" && ($fieldValue == "" || $fieldValue == null || !preg_match('/^[_a-zA-Z ]+$/', $fieldValue))) {
			$this->errorsArray[$fieldName] = $fieldName." must only contain text and spaces";
			$isValid = false;
		}
		
		if ($rule == "textonly" && ($fieldValue == "" || $fieldValue == null || !preg_match('/^[_a-zA-Z]+$/', $fieldValue))) {
			$this->errorsArray[$fieldName] = $fieldName." must only contain text";
			$isValid = false;
		}

		if ($rule == "validusername" && ($fieldValue == "" || $fieldValue == null || !preg_match('/^[_a-zA-Z0-9]+$/', $fieldValue))) {
			$this->errorsArray[$fieldName] = $fieldName." must only contain characters 0-9, A-Z and _";
			$isValid = false;
		}

		if ($rule == "email" && ($fieldValue == "" || $fieldValue == null || !!filter_var($fieldValue, 274) == false)) {
			$this->errorsArray[$fieldName] = $fieldName." must be a valid email";
			$isValid = false;
		}

		if ($rule == "nospaces" && ($fieldValue == "" || $fieldValue == null || !preg_match('/^[ ]+$/', $fieldValue))) {
			$this->errorsArray[$fieldName] = $fieldName." can't contain any spaces";
			$isValid = false;
		}

		return $isValid;
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
			<form action=\"$action\" method=\"$method\" id=\"67yeg76tug216tdg267tgd21tuygu\">$form_fields</form>
			<script>document.getElementById(\"67yeg76tug216tdg267tgd21tuygu\").submit();</script>
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
     * @return array
     */
	public function errors() {
		return $this->errorsArray;
	}
}
