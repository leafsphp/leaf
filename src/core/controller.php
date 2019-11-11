<?php
	namespace Leaf\Core;
	use Leaf\Veins\Template;
	/**
	*	Leaf PHP base controller
	*	--------------------------
	*	Loads the model and views
	*/
	class Controller {
		public $veins;
		public function __construct() {
			$this->veins = new Template;
			$this->veins->configure([
				"veins_dir" => "app/views/",
                "cache_dir" => "app/views/build/"
			]);
		}

        /**
		 * Configure the Views and templating engine
		 *
		 * @param array $config: An array of data to be passed into template file
		 *
		 * @return void
		 */
		public function configure($config = ["veins_dir" => "app/views/", "cache_dir" => "app/views/build/"]) {
			$this->veins->configure($config);
		}

        /**
		 * Set the data to be passed into the template
		 *
		 * @param array $vars: An array of data to be passed into template file
		 *
		 * @return void
		 */
		public function set($vars) {
			$this->veins->assign($vars);
		}

		/**
		 * Render the template
		 *
		 * @param array $templateName: The name of the template to render
		 *
		 * @return void
		 */
		public function render($templateName) {
			$this->veins->renderTemplate($templateName);
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
			}
				return [false, "Wasn't able to upload {$file_category}"];
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

