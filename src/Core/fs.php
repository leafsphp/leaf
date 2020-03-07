<?php
namespace Leaf\Core;

/**
 *  Leaf FileSystem
 *  --------
 *  Basic filesystem operations
 */
class FS {
	private $baseDirectory;

	public function __construct($baseDirectory) {
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
			$this->createFolder($this->baseDirectory);
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
	public function createFolder($dirname) {
		if (is_dir($dirname)) {
			echo "$dirname already exists in ".dirname($dirname);
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
	public function createFolderInBase($dirname) {
		if (is_dir($this->baseDirectory."/".$dirname)) {
			echo "$dirname already exists in $this->baseDirectory.";
			exit();
		}
		mkdir($this->baseDirectory."/".$dirname);
	}

	/**
	* Rename a directory
	*
	* @param string $dirname: the name of the folder to rename
	* @param string $newdirname: the new name of the folder
	*
	* @return void
	*/
	public function renameFolder($dirname, $newdirname) {
		if (!is_dir($dirname)) {
			echo "$dirname not found in ".dirname($dirname).".";
			exit();
		}
		// rename file
		rename($dirname, $newdirname);
	}


	/**
	* Delete a directory
	*
	* @param string $dirname: the name of the folder to delete
	*
	* @return void
	*/
	public function deleteFolder($dirname) {
		if (!is_dir($dirname)) {
			echo "$dirname not found in ".dirname($dirname).".";
			exit();
		}
		// delete folder
		rmdir($dirname);
	}

	
	/**
	* List all files and folders in directory
	*
	* @param string $dirname: the name of the directory to list
	*
	* @return void
	*/
	public function listDir($dirname = null, $pattern = null) {
		if ($dirname == null || $dirname == "") {
			$dirname = $this->baseDirectory || __DIR__;
		}
		// list dir content
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

	// just about done
	/**
	* Get the available space for disk
	*
	* @param string $dirname: the name of the directory to create
	*
	* @return integer free space in bits
	*/
	public function freeSpace($dirname = __DIR__) {
		if (!is_dir($dirname)) {
			echo "This directory doesn't exist";
			exit();
		}
		return \disk_free_space($dirname);
	}

	/**
	* Create a new file in the base directory
	*
	* @param string $filename: the name of the file to create
	*
	* @return void
	*/
	public function createFile($filename) {
		if (!is_dir($this->baseDirectory)) {
			$this->createFolder($this->baseDirectory);
		}
		if (file_exists($this->baseDirectory."/".$filename)) {
			touch($this->baseDirectory."/".time().".".$filename);
			return;
		}
		touch($this->baseDirectory."/".$filename);
	}


	/**
	* Write content to a file in the base directory
	*
	* @param string $filename: the name of the file to write to
	* @param $contant: the name of the file to write to
	*
	* @return void
	*/
	public function writeFile($filename, $content) {
		// ensure that file exists
		if (!file_exists($this->baseDirectory."/".$filename)) {
			$this->createFile($filename);
		}
		// write to file
		file_put_contents($this->baseDirectory."/".$filename, $content);
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
			exit();
		}
		// read file contents
		return file_get_contents($this->baseDirectory."/".$filename);
	}

	/**
	* Rename a file in the base directory
	*
	* @param string $filename: the name of the file to rename
	* @param string $newfilename: the new name of the file
	*
	* @return void
	*/
	public function renameFile($filename, $newfilename) {
		if (!file_exists($this->baseDirectory."/".$filename)) {
			echo "$filename not found in $this->baseDirectory. Change the base directory if you're sure the file exists.";
			exit();
		}
		// rename file
		rename($this->baseDirectory."/".$filename, $this->baseDirectory."/".$newfilename);
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
			exit();
		}
		// append data to file
		// read file
		$fileContent = $this->readFile($filename);
		// write to file
		$data = $fileContent."\n".$content;
		$this->writeFile($filename, $data);
	}

	// having few issues here
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
			exit();
		}
		unlink($this->baseDirectory."/".$filename);
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
	public function copyFile($filename, $to, $rename = true) {
		if (!file_exists($this->baseDirectory."/".$filename)) {
			echo "$filename not found in $this->baseDirectory. Change the base directory if you're sure the file exists.";
			exit();
		}
		$newfilename = $filename;
		if (file_exists($this->baseDirectory."/".$filename) && $rename == true) {
			$newfilename = "(".time().")".$filename;
		}
		try {
			copy($this->baseDirectory."/".$filename, $to."/".$newfilename);
		} catch (\Throwable $err) {
			throw "Unable to copy file: ".$err;
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
	public function copyToFile($filename, $to) {
		if (!file_exists($this->baseDirectory."/".$filename)) {
			echo "$filename not found in $this->baseDirectory. Change the base directory if you're sure the file exists.";
			exit();
		}
		try {
			copy($this->baseDirectory."/".$filename, $to);
		} catch (\Throwable $err) {
			throw "Unable to copy file: ".$err;
		}
	}


	// having issues here
	/**
	* Move a file from the base directory
	*
	* @param string $dirname: the name of the file to move
	*
	* @return void
	*/
	public function moveFile($filename, $to) {
		if (!file_exists($this->baseDirectory."/".$filename)) {
			echo "$filename not found in $this->baseDirectory. Change the base directory if you're sure the file exists.";
			exit();
		}
		move_uploaded_file($this->baseDirectory."/".$filename, $to);
	}
}