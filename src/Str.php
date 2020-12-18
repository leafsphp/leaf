<?php

namespace Leaf;

class Str extends \Illuminate\Support\Str
{
	/**
	 * Add a prefix to a string
	 */
	public static function prefix($prefix, $string)
	{
		$string = rtrim($prefix, '/') . '/' . ltrim($string, '/');
		$string = trim($string, '/');

		return $string;
	}

	// make shorthand method eg: 2000 ~ 2k, the ~ ē
	// make shorten eg shorten("I am Michael", 6) => "I am M..."
}
