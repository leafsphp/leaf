<?php
namespace Leaf\Core;

/**
 *  Leaf FileSystem
 *  --------
 *  Basic filesystem operations
 */
class FS {
	private $baseDirectory;

	public function __construct($baseDirectory = __DIR__) {
		$this->baseDirectory = $baseDirectory;
	}

	/**
	* Set the base directory for Leaf FS
	*
	* @param string $newBaseDirectory: Path to new base directory
	*
	* @return void
	*/
	public function setBaseDirectory($newBaseDirectory = __DIR__) {
		$this->baseDirectory = $newBaseDirectory;
		if (!is_dir($this->baseDirectory)) {
			$this->mkDir($this->baseDirectory);
		}
	}

	/**
	* Get the base directory for Leaf FS
	*
	* @return string base directory
	*/
	public function getBaseDirectory() {
		if ($this->baseDirectory == null || $this->baseDirectory == "") {
			return __DIR__;
		} else {
			return $this->baseDirectory;
		}
	}

	/**
	* Create a new directory in the same directory as current file(\_\_DIR\_\_)
	*
	* @param string $dirname: the name of the directory to create
	*
	* @return void
	*/
	public function mkDir($dirname) {
		if (is_dir($dirname)) {
			echo "$dirname already exists in $this->baseDirectory.";
			exit();
		}
		mkdir($dirname);
	}

	/**
	* Create a new directory in the base directory
	*
	* @param string $dirname: the name of the directory to create
	*
	* @return void
	*/
	public function mkDirInBase($dirname) {
		if (is_dir($this->baseDirectory."/".$dirname)) {
			echo "$dirname already exists in $this->baseDirectory.";
			exit();
		}
		mkdir($this->baseDirectory."/".$dirname);
	}

	// having problems here
	/**
	* List all files and folders in directory
	*
	* @param string $dirname: the name of the directory to create
	*
	* @return void
	*/
	public function listDir($dirname) {
		if ($dirname == null || $dirname == "") {
			$dirname = $this->baseDirectory || __DIR__;
		}
		// list dir content
		dir($dirname);
	}

	// just about done
	/**
	* Get the available space for disk
	*
	* @param string $dirname: the name of the directory to create
	*
	* @return integer free space in bits
	*/
	public function freeSpace($dirname = __DIR__) {
		return \disk_free_space($dirname);
	}

	/**
	* Create a new file in the base directory
	*
	* @param string $dirname: the name of the file to create
	*
	* @return void
	*/
	public function createFile($filename) {
		if (!is_dir($this->baseDirectory)) {
			$this->mkDir($this->baseDirectory);
		}
		if (file_exists($this->baseDirectory."/".$filename)) {
			touch($this->baseDirectory."/".time().".".$filename);
			return;
		}
		touch($this->baseDirectory."/".$filename);
	}

	// working on it
	/**
	* Write content to a file in the base directory
	*
	* @param string $dirname: the name of the file to create
	*
	* @return void
	*/
	public function writeFile($filename, $content) {
		// ensure that file exists
		if (!file_exists($this->baseDirectory."/".$filename)) {
			$this->createFile($filename);
		}
		// write to file
	}

	/**
	* Read the content of a file in the base directory
	*
	* @param string $dirname: the name of the file to read
	*
	* @return string file content
	*/
	public function readFile($filename) {
		if (!file_exists($this->baseDirectory."/".$filename)) {
			echo "$filename not found in $this->baseDirectory. Change the base directory if you're sure the file exists.";
		}
	}

	/**
	* Add to the content of a file in the base directory
	*
	* @param string $dirname: the name of the file to write to
	*
	* @return void
	*/
	public function appendFile($filename, $content) {
		if (!file_exists($this->baseDirectory."/".$filename)) {
			echo "$filename not found in $this->baseDirectory. Change the base directory if you're sure the file exists.";
		}
	}

	/**
	* Delete a file in the base directory
	*
	* @param string $dirname: the name of the file to delete
	*
	* @return void
	*/
	public function deleteFile($filename) {
		if (!file_exists($this->baseDirectory."/".$filename)) {
			echo "$filename not found in $this->baseDirectory. Change the base directory if you're sure the file exists.";
		}
	}

	/**
	* Copy and paste a file in the base directory
	*
	* @param string $dirname: the name of the file to copy
	*
	* @return void
	*/
	public function copyFile($filename) {
		if (!file_exists($this->baseDirectory."/".$filename)) {
			echo "$filename not found in $this->baseDirectory. Change the base directory if you're sure the file exists.";
		}
	}

	/**
	* Move a file from the base directory
	*
	* @param string $dirname: the name of the file to move
	*
	* @return void
	*/
	public function moveFile($filename) {
		if (!file_exists($this->baseDirectory."/".$filename)) {
			echo "$filename not found in $this->baseDirectory. Change the base directory if you're sure the file exists.";
		}
	}
}