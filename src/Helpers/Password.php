<?php

namespace Leaf\Helpers;

/**
 * Leaf Password Helper
 * ---------------------------------------------
 * Work easier and faster with passwords
 * 
 * @author Michael Darko <mychi.darko@gmail.com>
 * @since 2.0.0
 */
class Password
{
	public const BCRYPT = PASSWORD_BCRYPT;
	public const ARGON2 = PASSWORD_ARGON2I;
	public const DEFAULT = PASSWORD_DEFAULT;
	public const MD5 = "md5";

	/**Spice up an inputed password for better security */
	protected static $spice = null;

	/**
	 * Get or set password 'spice'
	 */
	public static function spice($spice = null)
	{
		if (!$spice) return static::$spice;
		static::$spice = $spice;
	}

	/**
	 * Create a password hash
	 * 
	 * See the [password algorithm constants](https://secure.php.net/manual/en/password.constants.php) for documentation on the supported options for each algorithm.
	 */
	public static function hash(string $password, $algorithm = self::DEFAULT, array $options = [])
	{
		return password_hash(static::$spice . $password, $algorithm, $options);
	}

	/** 
	 * Checks if the given hash matches the given options.
	 */
	public static function verify(string $password, $hashedPassword)
	{
		return password_verify(static::$spice . $password, $hashedPassword);
	}

	/**
	 * Generate an Argon2 hashed password
	 *  
	 * @param string $password The user's password to hash
	 * @param array $options Options for Argon hash
	 * 
	 * Supported Options:
	 * - memory_cost (integer) - Maximum memory (in bytes) that may be used to compute the Argon2 hash. Defaults to PASSWORD_ARGON2_DEFAULT_MEMORY_COST.
	 * - time_cost (integer) - Maximum amount of time it may take to compute the Argon2 hash. Defaults to PASSWORD_ARGON2_DEFAULT_TIME_COST.
	 * - threads (integer) - Number of threads to use for computing the Argon2 hash. Defaults to PASSWORD_ARGON2_DEFAULT_THREADS.
	 * 
	 * Available as of PHP 7.2.0.
	 */
	public static function argon2(string $password, array $options = [])
	{
		return password_hash(static::$spice . $password, self::ARGON2, $options);
	}

	/** 
	 * Checks if the given argon2 hash matches the given options.
	 */
	public static function argon2Verify(string $password, string $hashedPassword)
	{
		return password_verify(static::$spice . $password, $hashedPassword);
	}

	/**
	 * Generate an Argon2 hashed password
	 *  
	 * @param string $password The user's password to hash
	 * @param array $options Options for Argon hash
	 */
	public static function bcrypt(string $password, array $options = [])
	{
		return password_hash(static::$spice . $password, self::BCRYPT, $options);
	}

	/** 
	 * Checks if the given BCRYPT hash matches the given options.
	 */
	public static function bcryptVerify(string $password, string $hashedPassword)
	{
		return password_verify(static::$spice . $password, $hashedPassword);
	}
}
