<?php

class Wyn extends \Leaf\Wynter\Component {
	public function __construct()
	{
		$this->blade_config("app/pages/wynter", "app/pages/cache");
	}

	public function render()
	{
		echo "1 +1";
		echo $this->blade->render("wynter");
		// return $this->blade->render("wynter");
	}
}