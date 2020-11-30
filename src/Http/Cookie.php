<?php

namespace Leaf\Http;

/**
 * Leaf Cookie
 * ------------------------------------
 * Cookie management made simple with Leaf
 * 
 * @author Michael Darko
 * @since  2.0.0
 */
class Cookie
{
	protected static $options = [
		"expire" => 0,
		"path" => "",
		"domain" => "",
		"secure" => false,
		"httponly" => false
	];

	/**
	 * Set cookie
	 * 
	 * Set a new cookie
	 *
	 * @param string|array $key   Cookie name
	 * @param mixed  $value Cookie value
	 * @param array $options Cookie settings
	 */
	public static function set($key, $value = null, $options = [])
	{
		if (is_array($key)) {
			foreach ($key as $name => $value) {
				self::set($name, $value);
			}
		} else {
			setcookie(
				$key,
				$value,
				$options["expire"] ?? self::$options["expire"],
				$options["path"] ?? self::$options["path"],
				$options["domain"] ?? self::$options["domain"],
				$options["secure"] ?? self::$options["secure"],
				$options["httponly"] ?? self::$options["httponly"]
			);
		}
	}

	/**
	 * Shorthand method of setting a cookie + value + expire time
	 *
	 * @param string $name    The name of the cookie
	 * @param string $value   If string, the value of cookie; if array, properties for cookie including: value, expire, path, domain, secure, httponly
	 * @param string $expires When the cookie expires. Default: 7 days
	 */
	public static function simpleCookie($name, $value, $expires = "7 days")
	{
		self::set($name, $value, ["expire" => $expires]);
	}

	/**
	 * Get all set cookies
	 */
	public static function all()
	{
		return $_COOKIE;
	}

	/**
	 * Get a particular cookie
	 */
	public static function get($param)
	{
		return $_COOKIE[$param];
	}

	/**
	 * Unset a particular cookie
	 */
	public static function unset($key)
	{
		if (is_array($key)) {
			foreach ($key as $name) {
				self::unset($name);
			}
		} else {
			setcookie($key, "", time() - 86400);
		}
	}

	/**
	 * Unset all cookies
	 */
	public static function unsetAll()
	{
		foreach ($_COOKIE as $key => &$value) {
			self::unset($key);
		}
	}
}
