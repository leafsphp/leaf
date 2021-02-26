<?php

namespace Leaf;

/**
 * Leaf Util Class
 * ---------------------------------
 * Simple to use Utility methods
 * 
 * @author Michael Darko <mickdd22@gmail.com>
 * @since v2.2
 */
class Util
{
	public static function sanitize($data)
	{
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$data[self::sanitize($key)] = self::sanitize($value);
			}
		} else {
			$data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
		}

		return $data;
	}
}
