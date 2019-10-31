<?php
namespace Leaf\Config;

class Errors {
	public function hide() {
		error_reporting(0);
    	ini_set('display_errors', 0);
	}

	public function show() {
		error_reporting(1);
    	ini_set('display_errors', 1);
	}

	public function showCustom() {}
}