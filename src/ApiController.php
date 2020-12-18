<?php

namespace Leaf;

use Leaf\Http\Response;

/**
 * Leaf PHP base controller
 * --------------------------
 * Base API controller leaf php
 */
class ApiController extends Response
{
	public $response;
	public $form;

	public function __construct()
	{
		$this->form = new Form;
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
	 * Return the form errors
	 *
	 * @return array Any errors caught
	 */
	public function returnErrors()
	{
		return $this->form->errors();
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
		return \Leaf\FS::uploadFile($file, $path, $config);
	}
}
