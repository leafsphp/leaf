<?php

// namespace app;

use Leaf\Wynter\Component;

class WynterTest extends Component
{
    public $addOrEdit, $isOpen, $article, $showButton;

	protected $listeners = ['showModal' => 'open', 'closeModal' => 'close'];

	public function constructor()
	{
		$this->blade = new \Leaf\Blade("./app/pages/", "./app/pages/cache");
	}

    public function mount($showButton)
    {
        $this->isOpen = false;
        $this->showButton = $showButton;
        $this->addOrEdit = 'Add';
    }

    public function open($passedArticle = null)
    {
        $this->addOrEdit = 'Add';
        $this->isOpen = true;

        if ($passedArticle !== null) {
            $this->addOrEdit = 'Edit';
            $this->article = json_decode($passedArticle, true);
        }
    }

    public function close()
    {
        $this->isOpen = false;
        $this->initArticleDetails();
        $this->resetErrorBag();
    }


    public function render()
    {
		return $this->blade->render("test2");
    }
}
