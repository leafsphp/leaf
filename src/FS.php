<?php
namespace Leaf;

use Symfony\Component\Finder\Finder;

/**
 * Leaf FileSystem
 * --------
 * A simple and easy to use package that helps with basic
 * filesystem operations
 * 
 * @author  Michael Darko
 * @since   1.5.0
 */
class FS {
	/**Any errors caught in an FS operation */
	protected static $errorsArray = [];

	/**Details of a file(s) upload */
	protected static $uploadInfo = [];

	/**File extension types */
	protected static $extensions = [
		"image" => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'apng', 'tif', 'tiff', 'svg', 'pjpeg', 'pjp', 'jfif', 'cur', 'ico'],
		"video" => ['mp4', 'webm', 'swf', 'flv'],
		"audio" => ['wav', 'mp3', 'ogg', 'm4a'],
		"text" => ['txt', 'log', 'xml', 'doc', 'docx', 'odt', 'wpd', 'rtf', 'tex', 'pdf'],
		"presentation" => ['ppsx', 'pptx', 'ppt', 'pps', 'ppsm', 'key', 'odp'],
		"compressed" => ['zip', 'rar', 'bz', 'gz', 'iso', 'tar.gz', 'tgz', 'zipx', '7z', 'dmg'],
		"spreadsheet" => ['ods', 'xls', 'xlsx', 'xlsm'],
		"application" => ['apk', 'bat', 'cgi', 'pl', 'com', 'exe', 'gadget', 'jar', 'msi', 'py', 'wsf']
	];
	
	/**
	* Create a new directory in current directory (\_\_DIR\_\_)
	*
	* @param string $dirname: the name of the directory to create
	*
	* @return void
	*/
	public static function create_folder(String $dirname) {
		if (is_dir($dirname)) {
			self::$errorsArray[$dirname] = "$dirname already exists in " . dirname($dirname);
			return false;
		}
		mkdir($dirname);
	}

	/**
	* Rename a directory
	*
	* @param string $dirname: the name of the folder to rename
	* @param string $newdirname: the new name of the folder
	*
	* @return void
	*/
	public static function rename_folder(String $dirname, String $newdirname) {
		if (!is_dir($dirname)) {
			self::$errorsArray[$dirname] = "$dirname not found in " . dirname($dirname) . ".";
			return false;
		}
		rename($dirname, $newdirname);
	}


	/**
	* Delete a directory
	*
	* @param string $dirname: the name of the folder to delete
	*
	* @return void
	*/
	public static function delete_folder($dirname) {
		if (!is_dir($dirname)) {
			self::$errorsArray[$dirname] = "$dirname not found in " . dirname($dirname) . ".";
			return false;
		}
		rmdir($dirname);
	}

	
	/**
	* List all files and folders in a directory
	*
	* @param string $dirname: the name of the directory to list
	*
	* @return void
	*/
	public static function list_dir($dirname, $pattern = null) {		
		$files = glob($dirname . "/*$pattern*");
		$filenames = [];
        
        foreach ($files as $file) {
            $file = pathinfo($file);
			$filename = $file['filename'];
			
			if (isset($file['extension'])) {
				$extension = $file['extension'];
				array_push($filenames, "$filename.$extension");
			} else {
				array_push($filenames, "$filename/");
			}
		}
		
		return $filenames;
	}

	/**
     * Get an array of all files in a directory.
     *
     * @param  string  $directory
     * @param  bool  $hidden
     * @return \Symfony\Component\Finder\SplFileInfo[]
     */
    public static function list_files($directory, $hidden = false)
    {
        return iterator_to_array(
            Finder::create()->files()->ignoreDotFiles(!$hidden)->in($directory)->depth(0)->sortByName(),
            false
        );
	}

	/**
	 * Get all of the files from the given directory (recursive).
	 *
	 * @param  string  $directory
	 * @param  bool  $hidden
	 * @return \Symfony\Component\Finder\SplFileInfo[]
	 */
	public static function allFiles($directory, $hidden = false)
	{
		return iterator_to_array(
			Finder::create()->files()->ignoreDotFiles(!$hidden)->in($directory)->sortByName(),
			false
		);
	}

    /**
     * Get all of the directories within a given directory.
     *
     * @param  string  $directory
     * @return array
     */
    public static function list_folders($directory)
    {
        $directories = [];

        foreach (Finder::create()->in($directory)->directories()->depth(0)->sortByName() as $dir) {
            $directories[] = $dir->getPathname();
        }

        return $directories;
    }

	/**
	* Create a new file
	*
	* @param string $filename: the name of the file to create
	*
	* @return void
	*/
	public static function create_file($filename) {
		if (!is_dir(dirname($filename))) {
			self::create_folder(dirname($filename));
		}
		if (file_exists($filename)) {
			touch(time().".".$filename);
			return;
		}
		touch($filename);
	}

	/**
	 * Upload a file
	 * 
	 * @param string $file The file to upload
	 * @param string $path The path to save the file in
	 * @param string $file_category The type of file
	 * @param array $config Configuration options for file upload
	 * 
	 * @return string|bool
	 */
	public static function upload_file($file, $path, $config = [])
	{
		if (!is_dir($path)) {
			if (isset($config["verify_dir"]) && $config["verify_dir"] == true) {
				self::$errorsArray["upload"] = "Specified path '$path' does not exist";
				return false;
			} else {
				mkdir($path, 0777, true);
			}
		}

		if (isset($config["unique"]) && $config["unique"] == true) {
			$name = strtolower(strtotime(date("Y-m-d H:i:s")).'_'.str_replace(" ", "_", $file["name"]));
		} else {
			$name = str_replace(" ", "_", $file["name"]);
		}

		$temp = $file["tmp_name"];
		$size = $file["size"];
		$target_dir = $path;
		$target_file = $target_dir . basename($name);
		
		if (file_exists($target_file) && (isset($config["verify_file"]) && $config["verify_file"] == true)) {
			self::$errorsArray["upload"] = "$target_file already exists";
			return false;
		}

		$file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
		$maximum_file_size = $config["max_file_size"] ?? 10000000;
		$file_category = $config["file_type"] ?? self::get_category($file_type);

		if ($size > $maximum_file_size) {
			self::$errorsArray["upload"] = "maximum file exceeded, please choose a file smaller than 10mb";
			return false;
		}

		if (isset($config["validate"]) && $config["validate"] == true) {
			foreach (self::$extensions as $category => $exts) {
				if ($file_category == $category) $extensions = $exts;
			}

			if (!in_array($file_type, $extensions)) {
				self::$errorsArray["upload"] = $file['name'] . " format not acceptable for $file_category";
				return false;
			}
		}

		self::$uploadInfo[$name] = [
			"name" => $name,
			"size" => $size,
			"type" => $file_type,
			"category" => $file_category,
			"path" => $target_file,
			"parent_directory" => basename(dirname($target_file)),
			"parent_directory_path" => $target_dir
		];
		
		if (move_uploaded_file($temp, $target_file)) {
			return $name;
		} else {
			self::$errorsArray["upload"] = "Wasn't able to upload $file_category";
			return false;
		}
	}

	/**
	 * Get a file category from it's extension
	 */
	protected static function get_category(string $file_type)
	{
		foreach (self::$extensions as $category => $exts) {
			if (in_array($file_type, $exts)) return $category;
		}

		return 'file';
	}

	/**
	 * Get full information about an uploaded file
	 * 
	 * @param string|null $file The file info to get
	 */
	public static function upload_info($file = null) : array
	{
		return $file ? self::$uploadInfo[$file] : self::$uploadInfo;
	}

	/**
	 * Write content to a file
	 *
	 * @param string $filename the name of the file to write to
	 * @param mixed $content the name of the file to write to
	 * @param bool $lock Lock file?
	 *
	 * @return void
	 */
	public static function write_file($filename, $content, $lock = false) {
		if (!file_exists($filename)) {
			self::create_file($filename);
		}
		file_put_contents($filename, $content, $lock ? LOCK_EX : 0);
	}

	/**
	* Read the content of a file into a string
	*
	* @param String $filename: the name of the file to read
	*
	* @return String|false file content
	*/
	public static function read_file(String $filename) {
		if (!file_exists($filename)) {
			self::$errorsArray[$filename] = "$filename not found in " . dirname($filename);
			return false;
		}
		return file_get_contents($filename);
	}

	/**
	* Rename a file
	*
	* @param string $filename: the name of the file to rename
	* @param string $newfilename: the new name of the file
	*
	* @return void
	*/
	public static function rename_file($filename, $newfilename) {
		if (!file_exists($filename)) {
			self::$errorsArray[$filename] = "$filename not found in " . dirname($filename);
			return false;
		}
		rename($filename, $newfilename);
	}

	/**
	 * Delete a file
	 *
	 * @param string $dirname: the name of the file to delete
	 *
	 * @return void
	 */
	public static function delete_file($filename)
	{
		if (!file_exists($filename)) {
			self::$errorsArray[$filename] = "$filename not found in " . dirname($filename);
			return false;
		}
		unlink($filename);
	}

	/**
	 * Copy and paste a file
	 *
	 * @param string $filename: the name of the file to copy
	 * @param string $to: the directory to copy file to
	 * @param bool $rename: rename the file if another file exists with the same name
	 *
	 * @return void
	 */
	public static function copy_file($filename, $to, $rename = true)
	{
		if (!file_exists($filename)) {
			self::$errorsArray[$filename] = "$filename not found in " . dirname($filename);
			return false;
		}
		$newfilename = $filename;
		if (file_exists($filename) && $rename == true) {
			$newfilename = "(" . time() . ")" . $filename;
		}
		try {
			copy($filename, $to . "/" . $newfilename);
			return $newfilename;
		} catch (\Throwable $err) {
			self::$errorsArray[$filename] = "Unable to copy file";
			return false;
		}
	}

	/**
	 * Clone a file into a new file
	 *
	 * @param string $filename: the name of the file to copy
	 * @param string $to: the name of the new file to copy file to
	 *
	 * @return void
	 */
	public static function clone_file($filename, $to)
	{
		if (!file_exists($filename)) {
			self::$errorsArray[$filename] = "$filename not found in " . dirname($filename);
			return false;
		}
		try {
			copy($filename, $to);
		} catch (\Throwable $err) {
			throw "Unable to copy file: $err";
		}
	}

	/**
	 * Move a file
	 *
	 * @param string $dirname: the name of the file to move
	 *
	 * @return void
	 */
	public static function move_file($filename, $to)
	{
		if (!file_exists($filename)) {
			self::$errorsArray[$filename] = "$filename not found in " . dirname($filename);
			return false;
		}

		rename($filename, $to);
	}

	/**
	* Prepend data to a file
	*
	* @param string $filename: the name of the file to write to
	* @param string $content: the file content
	*
	* @return void
	*/
	public static function prepend($filename, $content) {
		if (!file_exists($filename)) {
			self::$errorsArray[$filename] = "$filename not found in " . dirname($filename);
			return false;
		}

		$fileContent = self::read_file($filename);
		$data = $content."\n".$fileContent;

		self::write_file($filename, $data);
	}

	/**
	 * Add to the content of a file
	 *
	 * @param string $filename: the name of the file to write to
	 * @param string $content: the file content
	 *
	 * @return void
	 */
	public static function append($filename, $content)
	{
		if (!file_exists($filename)) {
			self::$errorsArray[$filename] = "$filename not found in " . dirname($filename) . ". Change the base directory if you're sure the file exists.";
			return false;
		}
		
		file_put_contents($filename, $content, FILE_APPEND);
	}

	/**
	 * Get or set UNIX mode of a file or directory.
	 *
	 * @param  string  $path
	 * @param  int|null  $mode
	 * @return mixed
	 */
	public static function chmod($path, $mode = null)
	{
		if ($mode) {
			return chmod($path, $mode);
		}

		return substr(sprintf('%o', fileperms($path)), -4);
	}

	/**
	 * Create a symlink to the target file or directory. On Windows, a hard link is created if the target is a file.
	 *
	 * @param  string  $target
	 * @param  string  $link
	 * @return void
	 */
	public static function link($target, $link)
	{
		if (!windows_os()) {
			return symlink($target, $link);
		}

		$mode = is_dir($target) ? 'J' : 'H';

		exec("mklink /{$mode} " . escapeshellarg($link) . ' ' . escapeshellarg($target));
	}

	/**
	 * Extract the file name from a file path.
	 *
	 * @param  string  $path
	 * @return string
	 */
	public static function name($path)
	{
		return pathinfo($path, PATHINFO_FILENAME);
	}

	/**
	 * Extract the trailing name component from a file path.
	 *
	 * @param  string  $path
	 * @return string
	 */
	public static function basename($path)
	{
		return pathinfo($path, PATHINFO_BASENAME);
	}

	/**
	 * Extract the parent directory from a file path.
	 *
	 * @param  string  $path
	 * @return string
	 */
	public static function dirname($path)
	{
		return pathinfo($path, PATHINFO_DIRNAME);
	}

	/**
	 * Extract the file extension from a file path.
	 *
	 * @param  string  $path
	 * @return string
	 */
	public static function extension($path)
	{
		return pathinfo($path, PATHINFO_EXTENSION);
	}

	/**
	 * Get the file type of a given file.
	 *
	 * @param  string  $path
	 * @return string
	 */
	public static function type($path)
	{
		return filetype($path);
	}

	/**
	 * Get the file size of a given file.
	 *
	 * @param  string  $path
	 * @return int
	 */
	public static function size($path)
	{
		return filesize($path);
	}

	/**
	 * Return errors if any
	 * 
	 * @return array
	 */
	public static function errors(): array
	{
		return self::$errorsArray;
	}
}