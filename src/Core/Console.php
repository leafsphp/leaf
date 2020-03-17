<?php
namespace Leaf\Core;

class Console {
	public function log($data) {
		echo $data;
	}

	public function error($data) {
		echo $data;
		exit();
	}
}