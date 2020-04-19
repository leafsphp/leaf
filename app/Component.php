<?php
class Component extends \Leaf\Component {
	public $state = [];

	public function constructor() {
		$this->setState(["welcome" => "This is a simple component(beta) that uses state"]);
		$this->blade = new \Leaf\Blade("./app/pages/", "./app/pages/cache");
		$this->blade->directive('wynter', function ($expression) {
			return \Leaf\Wynter\BladeDirectives::wynter($expression);
		});
	}

	public function mounted() {
		// runs after constructor
		$state = $this->state;
		$state->welcome = "Welcome Message Changed";
		$this->setState($state);
	}

	public function main() {
		// equivalent of render in other frameworks (like react :-))
		echo $this->blade->render("test");
	}

	public function end() {
		// runs after main
	}
}