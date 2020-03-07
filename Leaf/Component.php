<?php
namespace Leaf;

/**
 * Leaf Vein Component
 * ---------------------
 * A "reactive" component that mimics frontend frameworks
 */
abstract class Component extends \Leaf\Veins {
	//  state variable to be used
	public $state;

	public function __construct() {
		$this->state = (object) $this->state;
	}

	public function constructor() {
		$this->state = (object) $this->state;
		// something happens when constructor is called
	}

	public function mounted() {
		// something happens when mounted is called
	}

	public function main() {
		// something happens when main is called
	}

	public function end() {
		// something happens when end is called
	}

	public function setState(array $data) {
		$this->state = (object) $data;
	}

	public function trigger() {
		$this->constructor();
		$this->mounted();
		$this->main();
		$this->end();
	}
}