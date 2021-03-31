<?php

namespace Leaf;

use Leaf\Http\Response;

/**
 * Leaf base controller
 * -----------------
 * Base controller for Leaf PHP Framework
 * 
 * @author Michael Darko <mickdd22@gmail.com>
 * @since 1.4.0
 * @version 2.0
 */
class Controller
{
	public $form;
	public $request;
	public $response;
	public $view;

	public function __construct()
	{
		$this->form = new Form;
		$this->request = new Http\Request;
		$this->response = new Http\Response;
		$this->view = new View;
	}

	/**
	 * Validate the given request with the given rules.
	 * 
	 * @param  array  $rules
	 */
	public function validate(array $rules)
	{
		$this->form->validate($rules);
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
