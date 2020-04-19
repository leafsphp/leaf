<?php
use Leaf\Wynter\Component;

class Test extends Component
{
	public $count = 0;

	public function __construct()
	{
		$this->blade = new \Leaf\Blade("./app/pages", "./app/pages/cache");
	}

	public function increase()
	{
		$this->count = $this->count + 1;
	}

	public function decrease()
	{
		$this->count = $this->count - 1;
	}

	public function render()
	{
		// echo $this->blade->render("test2", [
		// 	"count" => $this->count
		// ]);
		return $this->blade->render("test2", [
			"count" => $this->count
		]);
	}
}