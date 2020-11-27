<?php

namespace Leaf\Helpers;

/**
 * Leaf Authentication
 * ---------------------------------------------
 * Authentication helper for Leaf PHP
 * 
 * @author Michael Darko <mickdd22@gmail.com>
 * @since v1.2.0
 */
class Authentication
{
	/**
	 * Any errors caught
	 */
	protected static $errorsArray = [];

	/**
	 * Quickly generate a JWT encoding a user id
	 * 
	 * @param string $userId The user id to encode
	 * @param string $secretPhrase The user id to encode
	 * @param int $expiresAt Token lifetime
	 * 
	 * @return string The generated token
	 */
	public static function generateSimpleToken(string $userId, string $secretPhrase, int $expiresAt = null)
	{
		$payload = [
			'iat' => time(),
			'iss' => 'localhost',
			'exp' => time() + ($expiresAt ?? (60 * 60 * 24)),
			'user_id' => $userId
		];

		return self::generateToken($payload, $secretPhrase);
	}

	/**
	 * Create a JWT with your own payload
	 * 
	 * @param string $payload The JWT payload
	 * @param string $secretPhrase The user id to encode
	 * 
	 * @return string The generated token
	 */
	public static function generateToken(array $payload, string $secretPhrase)
	{
		return JWT::encode($payload, $secretPhrase);
	}

	/**
	 * Get Authorization Headers
	 */
	public static function getAuthorizationHeader()
	{
		$headers = null;

		if (isset($_SERVER['Authorization'])) {
			$headers = trim($_SERVER["Authorization"]);
		} else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
			$headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
		} else if (function_exists('apache_request_headers')) {
			$requestHeaders = apache_request_headers();
			// Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
			$requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));

			if (isset($requestHeaders['Authorization'])) {
				$headers = trim($requestHeaders['Authorization']);
			}
		}

		return $headers;
	}

	/**
	 * get access token from header
	 */
	public static function getBearerToken()
	{
		$headers = self::getAuthorizationHeader();

		if (!empty($headers)) {
			if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
				return $matches[1];
			}

			self::$errorsArray["token"] = "Access token not found";
			return null;
		}

		self::$errorsArray["token"] = "Access token not found";
		return null;
	}

	/**
	 * Validate and decode access token in header
	 */
	public static function validateToken($secretPhrase)
	{
		$bearerToken = self::getBearerToken();
		if ($bearerToken === null) return null;

		return self::validate($bearerToken, $secretPhrase);
	}

	/**
	 * Validate access token
	 * 
	 * @param string $token Access token to validate and decode
	 */
	public static function validate($token, $secretPhrase)
	{
		$payload = JWT::decode($token, $secretPhrase, ['HS256']);
		if ($payload !== null) return $payload;

		self::$errorsArray = array_merge(self::$errorsArray, JWT::errors());
		return null;
	}

	/**
	 * Get all authentication errors as associative array
	 */
	public static function errors()
	{
		return self::$errorsArray;
	}
}
