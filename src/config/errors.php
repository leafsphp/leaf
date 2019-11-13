<?php
namespace Leaf\Config;

/**
 * Leaf Dev Errors
 * ------
 * Easily handle run-time errors
 */
class Errors {
	/**
	 * Hide run time errors
	 */
	public function hide() {
		error_reporting(0);
   		ini_set('display_errors', 0);
	}

	/**
	 * Show run time errors
	 */
	public function show() {
		error_reporting(1);
   		ini_set('display_errors', 1);
	}

	public function showCustom() {}
}
