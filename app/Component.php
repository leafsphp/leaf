<?php
class Component extends \Leaf\Component {
	public function constructor() {
		$this->setState(["name" => "Michael Darko", "number" => 1]);
	}

	public function main() {
		echo $this->state->name."<br>";
		echo $this->state->number."<br>";
	}
}