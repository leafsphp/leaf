<?php
namespace Leaf\Core;

class Str extends \Illuminate\Support\Str {
	/**
	 * Add a prefix to a string
	 */
	public static function prefix($prefix, $string)
    {
        $string = rtrim($prefix, '/').'/'.ltrim($string, '/');

        $string = trim($string, '/');

        return $string;
	}
}