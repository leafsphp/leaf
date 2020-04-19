<?php
namespace Leaf\Helpers;

/**
 * Leaf Password Helper
 * ---------------------------------------------
 * Work easier and faster with passwords
 * 
 * @author Michael Darko <mychi.darko@gmail.com>
 * @since 2.0.1
 */
class Password {
	const BIN2HEX = "BIN2HEX";
	const BCRYPT = PASSWORD_BCRYPT;
	const BASE64 = "BASE64";
	const MD5 = "MD5";
	const SHA1 = "SHA1";
	const ARGON2 = PASSWORD_ARGON2I;

	/**Spice up an inputed password for better security */
	protected $salt;

	/**
	 * Get or set password 'salt'
	 */
	public function salt($salt = null) {
		if ($salt == null) {
			return $this->salt;
		}
		$this->salt = $salt;
	}

	/**
	 * Create a password hash
	 * 
	 * See the [password algorithm constants](https://secure.php.net/manual/en/password.constants.php) for documentation on the supported options for each algorithm.
	 */
	public static function hash(string $password, int $algorithm = self::BCRYPT, array $options = []) {
		return password_hash($password, $algorithm, $options);
	}

	/** 
	 * Checks if the given hash matches the given options.
	 */
	public static function verify(string $password, string $hash) {
		return password_verify($password, $hash);
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
	public static function argon2(string $password, array $options = []) {
		return password_hash($password, self::ARGON2, $options);
	}

	/** 
	 * Checks if the given argon2 hash matches the given options.
	 */
	public static function argon2_verify(string $password) {
		return password_verify($password, self::ARGON2);
	}

	/**
	 * Generate an Argon2 hashed password
	 *  
	 * @param string $password The user's password to hash
	 * @param array $options Options for Argon hash
	 */
	public static function bcrypt(string $password, array $options = [])
	{
		return password_hash($password, self::BCRYPT, $options);
	}

	/** 
	 * Checks if the given BCRYPT hash matches the given options.
	 */
	public static function bcrypt_verify(string $password)
	{
		return password_verify($password, self::BCRYPT);
	}

	/**
	 * Encode a password based on Leaf's Crux rule
	 * 
	 * @param string $password The password to encrypt
	 * @param mixed $hash_type_1 The Stronger Hash to use
	 * @param mixed $hash_type_2 The weaker has to use
	 * 
	 * @return string CRUX Encoded password
	 */
	public function crux(string $password, $hash_type_1 = self::ARGON2, $hash_type_2 = self::MD5) {
		$first_hash = "";
		$second_hash = "";

		if ($hash_type_1 === self::BCRYPT) {
			$first_hash = $this->hash($password, $hash_type_1);
		} else if ($hash_type_1 === self::ARGON2) {
			$first_hash = $this->hash($password, $hash_type_1);
		} else {
			$first_hash = $this->hash($password, self::BCRYPT);
		}

		if ($hash_type_2 === self::BASE64) {
			$second_hash = base64_encode($first_hash);
		} else {
			$second_hash = md5($first_hash);
		}

		return $second_hash;
	}

	/**
	 * Weaker faster encryptions
	 */
	public static function encrypt($data, $hash = self::BASE64) {
		if ($hash == self::BASE64) {
			// 
		} else if ($hash = self::MD5) {
			// 
		} else {
			// 
		}
	}
}