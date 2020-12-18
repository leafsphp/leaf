<?php

namespace Leaf\Helpers;

/**
 * Leaf Encryption Helper [BETA]
 * ------------------------------------------------
 * Easy encryptions
 * 
 * @author Michael Darko <mychi.darko@gmail.com>
 * @since 2.0.1
 */
class Encryption
{
	/**
	 * Key used for encryption: default generated with sodium
	 */
	protected $key;
	protected $nonce;

	public function __construct()
	{
		$this->setKeys(random_bytes(32), random_bytes(24));
	}

	/**
	 * Set encryption key and nonce
	 */
	public function setKeys($key, $nonce)
	{
		$this->key = $key;
		$this->nonce = $nonce;
		return $this;
	}

	/**
	 * Return the encryption key
	 */
	public function getKeys()
	{
		return [$this->key, $this->nonce];
	}

	/**
	 * Encrypt data using Sodium
	 */
	public function encrypt($data)
	{
		$ciphertext = sodium_crypto_secretbox($data, $this->nonce, $this->key);
		return base64_encode($this->nonce . $ciphertext);
	}

	/**
	 * Decrypt encrypted Sodium data
	 */
	public function decrypt($encrypted_data, $key, $nonce)
	{
		$ciphertext = mb_substr(base64_decode($encrypted_data), $nonce, null, '8bit');
		$secret_data = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
		return $secret_data;
	}
}
