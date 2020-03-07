<?php
namespace Leaf\Veins;

/**
 * Exception thrown when template file does not exists.
 */
class NotFoundException extends \Leaf\Http\Response {
	public function __construct() {
		echo 'Template file not found, make sure you correctly configured Vein....and named your files with the ".vein" extension';
	}
}


// -- end
