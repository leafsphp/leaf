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
	/**
	 * Default options for cookie values
	 */
	protected static $options = [
		'expires' => 0,
		'path' => '',
		'domain' => '',
		'secure' => false,
		'httponly' => false,
		'samesite' => 'Lax',
	];

	/**
	 * Set default cookie options
	 * 
	 * @param array $defaults
	 * ```
	 * 'expires' => 0,
	 * 'path' => ',
	 * 'domain' => ',
	 * 'secure' => false,
	 * 'httponly' => false,
	 * 'samesite' => 'Lax',
	 * ```
	 */
	public static function setDefaults(array $defaults)
	{
		static::$options = array_merge(
			static::$options,
			$defaults
		);
	}

	/**
	 * Set a new cookie
	 *
	 * @param string|array $key Cookie name
	 * @param mixed $value Cookie value
	 * @param array $options Cookie settings
	 */
	public static function set($key, $value = '', array $options = [])
	{
		if (is_array($key)) {
			foreach ($key as $name => $value) {
				self::set($name, $value);
			}
		} else {
			setcookie($key, $value, [
				'expires' => ($options['expire'] ?? $options['expires']) ?? self::$options['expire'],
				'path' => $options['path'] ?? self::$options['path'],
				'domain' => $options['domain'] ?? self::$options['domain'],
				'secure' => $options['secure'] ?? self::$options['secure'],
				'httponly' => $options['httponly'] ?? self::$options['httponly'],
				'samesite' => $options['samesite'] ?? self::$options['samesite'],
			]);
		}
	}

	/**
	 * Shorthand method of setting a cookie + value + expire time
	 *
	 * @param string $name The name of the cookie
	 * @param string $value If string, the value of cookie; if array, properties for cookie including: value, expire, path, domain, secure, httponly
	 * @param string $expires When the cookie expires. Default: 7 days
	 */
	public static function simpleCookie(string $name, string $value, string $expires = '7 days')
	{
		self::set($name, $value, ['expires' => $expires]);
	}

	/**
	 * Get all set cookies
	 */
	public static function all(): array
	{
		return $_COOKIE;
	}

	/**
	 * Get a particular cookie
	 */
	public static function get($param)
	{
		return $_COOKIE[$param] ?? null;
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
			setcookie($key, '', time() - 86400);
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
