<?php
class Component extends \Leaf\Component {
	public function constructor() {
		$this->setState(["name" => "Michael Darko", "number" => 1]);
		$this->blade = new \Leaf\Blade("./app/pages/", "./app/pages/cache");
		// $this->blade->directive('date', function ($expression, $data = null) {
		// 	$date = new \Leaf\Date;
		// 	return call_user_func($date->$expression, $data);
		// });
		$this->blade->directive('wynter', function ($expression) {
			return \Leaf\Wynter\BladeDirectives::wynter($expression);
		});
	}

	public function main() {
		echo $this->blade->render("test");
	}
}