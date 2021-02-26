<?php

namespace Leaf;

use Leaf\Http\Request;

/**
 * Leaf Forms
 * --------
 * Simple Form Validation with Leaf.
 * 
 * @since v1.0
 * @author Michael Darko <mickd22@gmail.com>
 */
class Form
{
	/**
	 * Array holding all caught errors
	 */
	protected static $errorsArray = [];

	/**
	 * Default and registered validation rules
	 */
	protected static $rules = [
		"required" => null,
		"number" => null,
		"text" => null,
		"textonly" => null,
		"validusername" => null,
		"username" => null,
		"email" => null,
		"nospaces" => null
	];

	public static function addError($field, $error)
	{
		static::$errorsArray[$field] = $error;
	}

	/**
	 * Load default rules
	 */
	protected static function rules()
	{
		$rules = [
			"required" => function ($field, $value) {
				if (($value == "" || $value == null)) {
					static::$errorsArray[$field] = "$field is required";
					return false;
				}
			},
			"number" => function ($field, $value) {
				if (($value == "" || $value == null || !preg_match('/^[0-9]+$/', $value))) {
					static::$errorsArray[$field] = "$field must only contain numbers";
					return false;
				}
			},
			"text" => function ($field, $value) {
				if (($value == "" || $value == null || !preg_match('/^[_a-zA-Z ]+$/', $value))) {
					static::$errorsArray[$field] = "$field must only contain text and spaces";
					return false;
				}
			},
			"textonly" => function ($field, $value) {
				if (($value == "" || $value == null || !preg_match('/^[_a-zA-Z]+$/', $value))) {
					static::$errorsArray[$field] = "$field must only contain text";
					return false;
				}
			},
			"validusername" => function ($field, $value) {
				if (($value == "" || $value == null || !preg_match('/^[_a-zA-Z0-9]+$/', $value))) {
					static::$errorsArray[$field] = $field . " must only contain characters 0-9, A-Z and _";
					return false;
				}
			},
			"username" => function ($field, $value) {
				if (($value == "" || $value == null || !preg_match('/^[_a-zA-Z0-9]+$/', $value))) {
					static::$errorsArray[$field] = "$field must only contain characters 0-9, A-Z and _";
					return false;
				}
			},
			"email" => function ($field, $value) {
				if (($value == "" || $value == null || !!filter_var($value, 274) == false)) {
					static::$errorsArray[$field] = "$field must be a valid email";
					return false;
				}
			},
			"nospaces" => function ($field, $value) {
				if (($value == "" || $value == null || !preg_match('/^[ ]+$/', $value))) {
					static::$errorsArray[$field] = "$field can't contain any spaces";
					return false;
				}
			},
			"max" => function ($field, $value, $params) {
				if (strlen($value) > $params) {
					static::$errorsArray[$field] = "$field can't be more than $params characters";
					return false;
				}
			},
			"min" => function ($field, $value, $params) {
				if (strlen($value) < $params) {
					static::$errorsArray[$field] = "$field can't be less than $params characters";
					return false;
				}
			}
		];
		
		static::$rules = array_merge(static::$rules, $rules);
	}

	/**
	 * Apply a form rule
	 */
	protected static function applyRule($rule)
	{
		$rulePart = explode(":", $rule);
		$mainRule = $rulePart[0];

		$supportedRules = static::supportedRules();

		if (!in_array($mainRule, $supportedRules)) {
			trigger_error("$mainRule  is not a supported rule. Supported rules are " . json_encode($supportedRules));
		}

		$formRule = static::$rules[$mainRule];

		if (count($rulePart) > 1) {
			return [$formRule, $rulePart[1]];
		}

		return $formRule;
	}

	/**
	 * Get a list of all supported rules.
	 * This includes default and custom rules.
	 */
	public static function supportedRules()
	{
		$supportedRules = [];

		foreach (static::$rules as $key => $value) {
			$supportedRules[] = $key;
		}

		return $supportedRules;
	}

	/**
	 * Define custom rules
	 */
	public static function rule($name, $handler = null)
	{
		if (is_array($name)) {
			static::$rules = array_merge(static::$rules, $name);
		} else {
			static::$rules[$name] = $handler;
		}
	}

	/**
	 * make sure that the form data is safe to work with
	 *
	 * @param string $data: The data gotten from the form field
	 *
	 * @return string
	 */
	public static function sanitizeInput($data)
	{
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
	public static function validate(array $rules, array $messages = [])
	{
		$fields = [];

		foreach ($rules as $param => $rule) {
			array_push($fields, ["name" => $param, "value" => Request::get($param), "rule" => $rule]);
		}

		foreach ($fields as $field) {
			if (is_array($field["rule"])) {
				foreach ($field["rule"] as $rule) {
					$rule = strtolower($rule);
					static::validateField($field["name"], $field["value"], $rule);
				}
			} else {
				$field["rule"] = strtolower($field["rule"]);
				static::validateField($field["name"], $field["value"], $field["rule"]);
			}
		}

		return (count(static::$errorsArray) === 0);
	}

	/**
	 * Validate data.
	 * 
	 * @param  array  $rules The data to be validated, plus rules
	 * @param  array  $messages
	 * 
	 * @return void
	 */
	public static function validateData(array $rules, array $messages = [])
	{
		$fields = [];

		foreach ($rules as $param => $rule) {
			array_push($fields, ["name" => $param, "value" => $param, "rule" => $rule]);
		}

		foreach ($fields as $field) {
			if (is_array($field["rule"])) {
				foreach ($field["rule"] as $rule) {
					$rule = strtolower($rule);	
					return static::validateField($field["name"], $field["value"], $rule);
				}
			} else {
				$field["rule"] = strtolower($field["rule"]);
				return static::validateField($field["name"], $field["value"], $field["rule"]);
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
	public static function validateField($fieldName, $fieldValue, $rule)
	{
		static::rules();

		$isValid = true;

		$data = static::applyRule($rule);
		
		if (is_array($data)) {
			$data = $data[0]($fieldName, $fieldValue, $data[1] ?? null);
		} else {
			$data = $data($fieldName, $fieldValue);
		}

		if ($data === false) {
			$isValid = false;
		}

		return $isValid;
	}

	/**
	 * Directly "submit" a form without having to work with any mark-up
	 */
	public static function submit(string $method, string $action, array $fields)
	{
		$form_fields = "";

		foreach ($fields as $key => $value) {
			$form_fields = $form_fields . "<input type=\"hidden\" name=\"$key\" value=" . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . ">";
		}

		echo "
			<form action=\"$action\" method=\"$method\" id=\"67yeg76tug216tdg267tgd21tuygu\">$form_fields</form>
			<script>document.getElementById(\"67yeg76tug216tdg267tgd21tuygu\").submit();</script>
		";
	}

	public static function isEmail($value)
	{
		return !!filter_var($value, 274);
	}

	/**
	 * Return the form fields+data
	 *
	 * @return string
	 */
	public static function body()
	{
		return Request::body();
	}

	/**
	 * Return the form fields+data
	 *
	 * @return string
	 */
	public static function get()
	{
		return Request::body();
	}

	/**
	 * Return the form errors
	 *
	 * @return array
	 */
	public static function errors()
	{
		return static::$errorsArray;
	}
}
