<?php

if (!function_exists('app')) {
	/**
	 * Return the Leaf instance
	 * 
	 * @return Leaf\App
	 */
	function app()
	{
		global $app;

		if (!$app) {
			$app = new Leaf\App;
		}

		return $app;
	}
}

if (!function_exists('auth')) {
	/**
	 * Return Leaf's auth object
	 */
	function auth($guard = null)
	{
		if (!class_exists("Leaf\Auth")) {
			trigger_error("Leaf auth module not installed. Run `composer require leafs/auth` to install.");
		}

		if (!class_exists("Leaf\Http\Session")) {
			trigger_error("Leaf session module not installed. Run `composer require leafs/session` to install.");
		}

		if (!$guard) return \Leaf\Auth::class;

		if ($guard === 'session') {
			return \Leaf\Auth::session();
		}

		return \Leaf\Auth::guard($guard);
	}
}

if (!function_exists('d')) {
	/**
	 * Return Leaf's date object
	 * 
	 * @return Leaf\Date
	 */
	function d()
	{
		if (!class_exists("Leaf\Date")) {
			trigger_error("Leaf date module not installed. Run `composer require leafs/date` to install.");
		}

		return app()->date;
	}
}

if (!function_exists('db')) {
	/**
	 * Return a db row by it's id
	 *
	 * @param string $table The table to find row
	 * @param string|int $row_id The row's id
	 * @param string $columns The columns to get
	 *
	 * @return array|null/Leaf\Db
	 */
	function db($table = null)
	{
		if (!class_exists("Leaf\Db")) {
			trigger_error("Leaf db module not installed. Run `composer require leafs/db` to install.");
		}

		app()->db()->autoConnect();

		if (!$table) {
			return app()->db();
		}

		return app()->db()->select($table)->fetchAll();
	}
}

if (!function_exists('email')) {
	/**
	 * Write and send an email
	 *
	 * @param array $email The email block to write and send
	 */
	function email(array $email)
	{
		if (!class_exists("Leaf\Mail")) {
			trigger_error("Leaf mail module not installed. Run `composer require leafs/mail` to install.");
		}

		$mail = new \Leaf\Mail;
		if (getenv("MAIL_DRIVER") === "smtp") {
			$mail->smtp_connect(
				getenv("MAIL_HOST"),
				getenv("MAIL_PORT"),
				!getenv("MAIL_USERNAME") ? false : true,
				getenv("MAIL_USERNAME") ?? null,
				getenv("MAIL_PASSWORD") ?? null,
				getenv("MAIL_ENCRYPTION") ?? "STARTTLS"
			);
		}
		$mail->write($email)->send();
	}
}

if (!function_exists('_env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function _env($key, $default = null)
    {
        $item = getenv($key);

        if (!isset($_ENV[$key]) || (isset($_ENV[$key]) && $_ENV[$key] == null)) {
            $item = $default;
        }

        return $item;
    }
}

if (!function_exists('fs')) {
	/**
	 * Return Leaf's FS object
	 */
	function fs()
	{
		if (!class_exists("Leaf\FS")) {
			trigger_error("Leaf fs module not installed. Run `composer require leafs/fs` to install.");
		}

		return app()->fs;
	}
}

if (!function_exists('flash')) {
    /**
     * Return Leaf's flash object
     */
    function flash()
    {
		if (!class_exists("Leaf\Http\Session")) {
			trigger_error("Leaf session module not installed. Run `composer require leafs/session` to install.");
		}

        return \Leaf\Flash::class;
    }
}

if (!function_exists('hasAuth')) {
	/**
	 * Find out if there's an active sesion
	 */
	function hasAuth()
	{
		return !!sessionUser();
	}
}

if (!function_exists('json')) {
	/**
	 * json uses Leaf's now `json` method
	 *
	 * json() packs in a bunch of functionality and customization into one method
	 *
	 * @param array|string|object $data The data to output
	 * @param int $code HTTP Status code for response, it's set in header
	 * @param bool $showCode Show response code in response body?
	 * @param bool $useMessage Show code meaning instead of int in response body?
	 */
	function json($data, int $code = 200, bool $showCode = false, bool $useMessage = false)
	{
		app()->response()->json($data, $code, $showCode, $useMessage);
	}
}

if (!function_exists('markup')) {
	/**
	 * Output markup as response
	 *
	 * @param string $data The markup to output
	 * @param int $code The http status code
	 */
	function markup($data, $code = 200)
	{
		app()->response()->markup($data, $code);
	}
}

if (!function_exists('request')) {
	/**
	 * Return request or request data
	 *
	 * @param array|string $data — Get data from request
	 */
	function request($data = null)
	{
		if ($data) return app()->request()->get($data);
		return app()->request();
	}
}

if (!function_exists('requestBody')) {
	/**
	 * Get request body
	 *
	 * @param bool $safeData — Sanitize output
	 */
	function requestBody($safeOutput = true)
	{
		return request()->body($safeOutput);
	}
}

if (!function_exists('requestData')) {
	/**
	 * Get request data
	 *
	 * @param string|array $param The item(s) to get from request
	 * @param bool $safeData — Sanitize output
	 */
	function requestData($param, $safeOutput = true, $assoc = false)
	{
		$data = request()->get($param, $safeOutput);
		return $assoc && is_array($data) ? array_values($data) : $data;
	}
}

if (!function_exists('response')) {
	/**
	 * Return response or set response data
	 *
	 * @param array|string $data — The JSON response to set
	 */
	function response($data = null)
	{
		if ($data) return app()->response()->json($data);
		return app()->response();
	}
}

if (!function_exists('Route')) {
	/**
	 * @param string The request method(s)
	 * @param string The route to handle
	 * @param callable|string The handler for the route
	 */
	function Route($methods, $pattern, $fn)
	{
		app()->match($methods, $pattern, $fn);
	}
}

if (!function_exists('session')) {
	/**
	 * Get a session variable or the session object
	 *
	 * @param string|null $key The variable to get
	 */
	function session($key = null)
	{
		if (!class_exists("Leaf\Http\Session")) {
			trigger_error("Leaf session module not installed. Run `composer require leafs/session` to install.");
		}

		if ($key) {
			return \Leaf\Http\Session::get($key);
		}

		return (new \Leaf\Http\Session);
	}
}

if (!function_exists('sessionUser')) {
	/**
	 * Get the currently logged in user
	 */
	function sessionUser()
	{
		if (!class_exists("Leaf\Http\Session")) {
			trigger_error("Leaf session module not installed. Run `composer require leafs/session` to install.");
		}

		return session('AUTH_USER');
	}
}

if (!function_exists('setHeader')) {
	/**
	 * Set a response header
	 *
	 * @param string|array $key The header key
	 * @param string $value Header value
	 * @param bool $replace Replace header if exists
	 * @param mixed|null $code Status code
	 */
	function setHeader($key, $value = "", $replace = true, $code = 200)
	{
		app()->headers()->set($key, $value, $replace, $code);
	}
}

if (!function_exists('throwErr')) {
	/**
	 * @param mixed $error The error to output
	 * @param int $code Http status code
	 * @param bool $useMessage Use message in response body
	 */
	function throwErr($error, int $code = 500, bool $useMessage = false)
	{
		app()->response()->throwErr($error, $code, $useMessage);
	}
}
