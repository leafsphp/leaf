<?php
namespace Leaf;

use Symfony\Component\Finder\Finder;

/**
 *  Leaf FileSystem
 *  --------
 *  Basic filesystem operations
 */
class FS {
	/**
	* Create a new directory in current directory (\_\_DIR\_\_)
	*
	* @param string $dirname: the name of the directory to create
	*
	* @return void
	*/
	public function create_folder(String $dirname) {
		if (is_dir($dirname)) {
			echo "$dirname already exists in " . dirname($dirname);
			exit();
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
	public function rename_folder(String $dirname, String $newdirname) {
		if (!is_dir($dirname)) {
			echo "$dirname not found in " . dirname($dirname) . ".";
			exit();
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
	public function delete_folder($dirname) {
		if (!is_dir($dirname)) {
			echo "$dirname not found in " . dirname($dirname) . ".";
			exit();
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
	public function list_dir($dirname, $pattern = null) {		
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
    public function list_dir_files($directory, $hidden = false)
    {
        return iterator_to_array(
            Finder::create()->files()->ignoreDotFiles(! $hidden)->in($directory)->depth(0)->sortByName(),
            false
        );
    }

    /**
     * Get all of the directories within a given directory.
     *
     * @param  string  $directory
     * @return array
     */
    public function list_dir_folders($directory)
    {
        $directories = [];

        foreach (Finder::create()->in($directory)->directories()->depth(0)->sortByName() as $dir) {
            $directories[] = $dir->getPathname();
        }

        return $directories;
    }

	/**
	* Create a new file in the base directory
	*
	* @param string $filename: the name of the file to create
	*
	* @return void
	*/
	public function create_file($filename) {
		if (!is_dir($this->baseDirectory)) {
			$this->create_folder($this->baseDirectory);
		}
		if (file_exists($filename)) {
			touch(time().".".$filename);
			return;
		}
		touch($filename);
	}

	public function upload_file($path, $file, $file_category = "image"): Array {
		// get file details
		$name = strtolower(strtotime(date("Y-m-d H:i:s")).'_'.str_replace(" ", "",$file["name"]));
		$temp = $file["tmp_name"];
		$size = $file["size"];

		$target_dir = $path; // destination path
		$target_file = $target_dir . basename($name); // destination file
		$upload_ok = true; // upload checker
		$file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION)); // file type

			
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
			return [false, $file['name']." format not acceptable for $file_category"];
		}
		// Check if $upload_ok is set to 0 by an error
		if (move_uploaded_file($temp, $target_file)) {
			return [true, $name];
		} else {
			return [false, "Wasn't able to upload {$file_category}"];
		}
	}

	/**
	* Write content to a file in the base directory
	*
	* @param string $filename: the name of the file to write to
	* @param $content: the name of the file to write to
	*
	* @return void
	*/
	public function write_file($filename, $content) {
		if (!file_exists($filename)) {
			$this->create_file($filename);
		}
		file_put_contents($filename, $content);
	}

	/**
	* Read the content of a file in the base directory
	*
	* @param string $dirname: the name of the file to read
	*
	* @return string file content
	*/
	public function read_file($filename) {
		if (!file_exists($filename)) {
			echo "$filename not found in $this->baseDirectory. Change the base directory if you're sure the file exists.";
			exit();
		}
		return file_get_contents($filename);
	}

	/**
	* Rename a file in the base directory
	*
	* @param string $filename: the name of the file to rename
	* @param string $newfilename: the new name of the file
	*
	* @return void
	*/
	public function rename_file($filename, $newfilename) {
		if (!file_exists($filename)) {
			echo "$filename not found in $this->baseDirectory. Change the base directory if you're sure the file exists.";
			exit();
		}
		rename($filename, $newfilename);
	}

	/**
	 * Delete a file in the base directory
	 *
	 * @param string $dirname: the name of the file to delete
	 *
	 * @return void
	 */
	public function delete_file($filename)
	{
		if (!file_exists($filename)) {
			echo "$filename not found in $this->baseDirectory. Change the base directory if you're sure the file exists.";
			exit();
		}
		unlink($filename);
	}

	/**
	 * Copy and paste a file from the base directory
	 *
	 * @param string $filename: the name of the file to copy
	 * @param string $to: the directory to copy file to
	 * @param bool $rename: rename the file in dir after copyinng or override
	 *
	 * @return void
	 */
	public function copy_file($filename, $to, $rename = true)
	{
		if (!file_exists($filename)) {
			echo "$filename not found in $this->baseDirectory. Change the base directory if you're sure the file exists.";
			exit();
		}
		$newfilename = $filename;
		if (file_exists($filename) && $rename == true) {
			$newfilename = "(" . time() . ")" . $filename;
		}
		try {
			copy($filename, $to . "/" . $newfilename);
		} catch (\Throwable $err) {
			throw "Unable to copy file: " . $err;
		}
	}

	/**
	 * Copy and paste a file from the base directory into a new file
	 *
	 * @param string $filename: the name of the file to copy
	 * @param string $to: the name of the new file to copy file to
	 *
	 * @return void
	 */
	public function copy_to_file($filename, $to)
	{
		if (!file_exists($filename)) {
			echo "$filename not found in $this->baseDirectory. Change the base directory if you're sure the file exists.";
			exit();
		}
		try {
			copy($filename, $to);
		} catch (\Throwable $err) {
			throw "Unable to copy file: $err";
		}
	}

	/**
	 * Move a file from the base directory
	 *
	 * @param string $dirname: the name of the file to move
	 *
	 * @return void
	 */
	public function move_file($filename, $to)
	{
		if (!file_exists($filename)) {
			echo "$filename not found in $this->baseDirectory. Change the base directory if you're sure the file exists.";
			exit();
		}

		rename($this->baseDirectory . "/" . $filename, $to);
	}

	/**
	* Prepend data to a file in the base directory
	*
	* @param string $filename: the name of the file to write to
	* @param string $content: the file content
	*
	* @return void
	*/
	public function prepend($filename, $content) {
		if (!file_exists($filename)) {
			echo "$filename not found in $this->baseDirectory. Change the base directory if you're sure the file exists.";
			exit();
		}
		// append data to file
		// read file
		$fileContent = $this->read_file($filename);
		// write to file
		$data = $content."\n".$fileContent;
		$this->write_file($filename, $data);
	}

	/**
	 * Add to the content of a file in the base directory
	 *
	 * @param string $filename: the name of the file to write to
	 * @param string $content: the file content
	 *
	 * @return void
	 */
	public function append($filename, $content)
	{
		if (!file_exists($this->baseDirectory . "/" . $filename)) {
			echo "$filename not found in $this->baseDirectory. Change the base directory if you're sure the file exists.";
			exit();
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
	public function chmod($path, $mode = null)
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
	public function link($target, $link)
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
	public function name($path)
	{
		return pathinfo($path, PATHINFO_FILENAME);
	}

	/**
	 * Extract the trailing name component from a file path.
	 *
	 * @param  string  $path
	 * @return string
	 */
	public function basename($path)
	{
		return pathinfo($path, PATHINFO_BASENAME);
	}

	/**
	 * Extract the parent directory from a file path.
	 *
	 * @param  string  $path
	 * @return string
	 */
	public function dirname($path)
	{
		return pathinfo($path, PATHINFO_DIRNAME);
	}

	/**
	 * Extract the file extension from a file path.
	 *
	 * @param  string  $path
	 * @return string
	 */
	public function extension($path)
	{
		return pathinfo($path, PATHINFO_EXTENSION);
	}

	/**
	 * Get the file type of a given file.
	 *
	 * @param  string  $path
	 * @return string
	 */
	public function type($path)
	{
		return filetype($path);
	}

	/**
	 * Get the file size of a given file.
	 *
	 * @param  string  $path
	 * @return int
	 */
	public function size($path)
	{
		return filesize($path);
	}
}