<?php
namespace Leaf\JS;

/**
 * Leaf JS Scripts [BETA]
 * ------------------------
 * Methods which render JavaScript methods/objects
 */
class Scripts {
	/**
	 * Console.log() with PHP
	 */
	public static function c_log(...$data) {
		// if (is_array($data)) $data = json_encode($data);
		$output = "";
		foreach ($data as $item) {
			$output = $output === "" ? "$item" : "$output, $item";
		}
		echo <<<EOT
<script>console.log(`$output`);</script>
EOT;
	}

	/**
	 * localstorage.set() with PHP
	 */
	public static function local_storage_set($key, $data) {
		if (is_array($data)) $data = json_encode($data);
		echo <<<EOT
<script>window.localstorage.set($key, $data);</script>
EOT;
	}

	/**
	 * localstorage.get() with PHP
	 */
	public static function local_storage_get($key) {
		echo <<<EOT
<script>window.localstorage.get($key);</script>
EOT;
	}

	/**
	 * localstorage.remove() with PHP
	 */
	public static function local_storage_remove($key) {
		echo <<<EOT
<script>window.localstorage.remove($key);</script>
EOT;
	}

	/**
	 * localstorage.clear() with PHP
	 */
	public static function local_storage_clear() {
		echo <<<EOT
<script>window.localstorage.clear();</script>
EOT;
	}
}