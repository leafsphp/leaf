<?php

namespace Leaf;

use Leaf\Http\Response;

/**
 *	Leaf PHP base controller
 *	--------------------------
 *	Base controller for Leaf PHP Framework
 */
class Controller extends Response
{
	public $blade;
	public $form;
	public $request;

	public function __construct()
	{
		$this->blade = new \Leaf\Blade;
		$this->blade->configure("app/views/", "storage/framework/views/");
		$this->form = new Form;
		$this->request = new \Leaf\Http\Request;
	}

	/**
	 * Configure the Views and templating engine
	 *
	 * @param array $views: Path to locate templates
	 * @param array $cache: Path to save templating cache
	 *
	 * @return void
	 */
	public function configure($views = "app/views/", $cache = "storage/framework/views/")
	{
		$this->blade->configure($views, $cache);
	}

	/**
	 * Validate the given request with the given rules.
	 * 
	 * @param  array  $rules
	 * @param  array  $messages
	 * 
	 * @return void
	 */
	public function validate(array $rules, array $messages = [])
	{
		$this->form->validate($rules, $messages);
	}

	/**
	 * Render the template
	 *
	 * @param array $templateName: The name of the template to render
	 *
	 * @return void
	 */
	public function render(string $templateName, array $data = [], array $merge_data = [])
	{
		$this->blade->render($templateName, $data, $merge_data);
	}

	/**
	 * Upload a file
	 * 
	 * @param string $file The file to upload
	 * @param string $path The path to save the file in
	 * @param array $config Configuration options for file upload
	 * 
	 * @return string|bool
	 */
	public function fileUpload($file, $path, $config = [])
	{
		return \Leaf\Fs::uploadFile($file, $path, $config);
	}
}
