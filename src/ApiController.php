<?php
namespace Leaf;

use Leaf\Http\Response;

/**
*	Leaf PHP base controller
*	--------------------------
*	Base API controller leaf php
*/
class ApiController extends Response {
	public $response;
	public $form;
	public function __construct() {
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
	public function validate(array $rules, array $messages = []) {
		$this->form->validate($rules, $messages);
	}

	/**
	 * Return the form errors
	 *
	 * @return string, $message: The to add to the errors array
	 */
	public function returnErrors() {
		return $this->form->errors();
	}

	/**
	 * Upload a file
	 * 
	 * @param string $path The path to save the file in
	 * @param string $file The file to upload
	 * @param array $config Configuration options for file upload
	 * 
	 * @return string|bool
	 */
	public function file_upload($path, $file, $config = []) {
		return \Leaf\Fs::upload_file($path, $file, $config);
	}
}
