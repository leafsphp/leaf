<?php

namespace Leaf\JS;

/**
 * Leaf JS Scripts [BETA]
 * ------------------------
 * Methods which render JavaScript methods/objects
 * 
 * @author Michael Darko <https://mychi.netlify.com>
 * @since 2.0
 */
class Scripts
{
	/**
	 * Console.log() with PHP
	 */
	public static function c_log(...$data)
	{
		$output = "";
		foreach ($data as $item) {
			if (is_array($item)) $item = json_encode($item);
			$output = $output === "" ? "$item" : "$output $item";
		}
		echo <<<EOT
<script>console.log(`$output`);</script>
EOT;
	}

	/**
	 * console.log() with css in PHP
	 */
	public static function c_style($data, $css)
	{
		// if (is_array($data)) $data = json_encode($data);
		if (is_array($data)) $data = json_encode($data);
		echo <<<EOT
<script>console.log(`%c $data`, `$css`);</script>
EOT;
	}

	/**
	 * Console.trace
	 */
	public static function c_trace($data)
	{
		// get the current function's name
		if (is_array($data)) $data = json_encode($data);
		echo <<<EOT
<script>console.trace(`$data`);</script>
EOT;
	}

	/**
	 * Console.table
	 */
	public static function c_table($data)
	{
		$data = json_encode($data);
		echo <<<EOT
<script>console.table(JSON.parse(JSON.stringify($data)));</script>
EOT;
	}

	/**
	 * Clear the console
	 */
	public static function c_clear()
	{
		echo <<<EOT
<script>console.clear();</script>
EOT;
	}

	/**
	 * Console.debug
	 */
	public static function c_debug()
	{
		echo <<<EOT
<script>console.debug();</script>
EOT;
	}

	/**
	 * localStorage.set() with PHP
	 */
	public static function localstorage_set($key, $data)
	{
		if (is_array($data)) $data = json_encode($data);
		echo <<<EOT
<script>window.localStorage.setItem(`$key`, `$data`);</script>
EOT;
	}

	/**
	 * localStorage.get() with PHP
	 */
	public static function localstorage_get($key)
	{
		echo <<<EOT
<script>window.localStorage.getItem(`$key`);</script>
EOT;
	}

	/**
	 * localStorage.remove() with PHP
	 */
	public static function localstorage_remove($key)
	{
		echo <<<EOT
<script>window.localStorage.removeItem(`$key`);</script>
EOT;
	}

	/**
	 * localStorage.clear() with PHP
	 */
	public static function localstorage_clear()
	{
		echo <<<EOT
<script>window.localStorage.clear();</script>
EOT;
	}
}
