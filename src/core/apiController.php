<?php
	namespace Leaf\Core;

	use Leaf\Core\Http\Response;
	use Leaf\Core\Form;
	/**
	*	Leaf PHP base controller
	*	--------------------------
	*	Loads the model and views
	*/
	class ApiController {
		public $response;
		public $form;
		public function __construct() {
			$this->response = new Response;
			$this->form = new Form;
		}

        /**
		 * Submit JSON encoded data to  user
		 *
		 * @param array $data: An array of data to be displayed to the user
		 *
		 * @return void
		 */
		public function respond($data) {
			$this->response->respond($data);
		}
        
        /**
		 * Submit JSON encoded data to  user with an http code
		 *
		 * @param array $data: An array of data to be displayed to the user
		 * @param integer $code: An array of data to be displayed to the user
		 *
		 * @return void
		 */
		public function respondWithCode($data, $code) {
			$this->response->respondWithCode($data, $code);
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
			return $this->form->returnErrors();
		}

		public function file_upload($path, $file, $file_category = "image"): Array {
			// get file details
			$name = strtolower(strtotime(date("Y-m-d H:i:s")).'_'.str_replace(" ", "",$file["name"]));
			$temp = $file["tmp_name"];
			$size = $file["size"];

			$target_dir     =   $path;                                                  // destination path
			$target_file    =   $target_dir . basename($name);                    // destination file
			$upload_ok      =   true;                                                      // upload checker
			$file_type      =   strtolower(pathinfo($target_file, PATHINFO_EXTENSION));  // file type

				
			if (!file_exists($path)):
				mkdir($path, 0777, true);
			endif;
			
			// Check if file already exists
			if (file_exists($target_file)) {
				return [true, $name];
			}
			// Check file size
			if ($size > 2000000) {
				return [false, "file too big"];
			}
			// Allow certain file formats
			switch ($file_category) {
				case 'image':
					$extensions = ['jpg', 'jpeg', 'png', 'gif'];
					break;
				
				case 'audio':
					$extensions = ['wav', 'mp3', 'ogg', 'wav', 'm4a'];
					break;
			}

			if (!in_array($file_type, $extensions)) {
				return [false, $file['name']." format not acceptable"];
			}
			// Check if $upload_ok is set to 0 by an error
			if (move_uploaded_file($temp, $target_file)) {
				return [true, $name];
			} else {
				return [false, "Wasn't able to upload {$file_category}"];
			}
		}

		function startsWith($haystack, $needle): Bool {
			$length = strlen($needle);
			return (substr($haystack, 0, $length) === $needle);
		}

		public function objectify($array): Object {
			return \json_decode(\json_encode($array));
		}

		public function permit($array, $permitables): Array {
			$permits = [];

			foreach ($permitables as $key => $permitable) {
				$permits[$permitable] = $array[$permitable];
			}

			return $permits;
		}
	}

