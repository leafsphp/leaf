<?php

if (!function_exists('app')) {
	/**
	 * Return the Leaf instance
	 * 
	 * @return Leaf\App
	 */
	function app()
	{
		$app = Leaf\Config::get("app")["instance"] ?? null;

		if (!$app) {
			$app = new Leaf\App;
			Leaf\Config::set("app", ["instance" => $app]);
		}

		return $app;
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
