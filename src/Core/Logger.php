<?php
namespace Leaf\Core;

/**
 *  Leaf Logger
 *  --------
 *  Simple logging
 */
class Logger extends FS {
	private $supportedTypes = [
		"debug", "info", "notice",  "warning", "error", "critical", "alert", "emergency"
	];

	public function __construct($dirname = __DIR__) {
		$this->setBaseDirectory($dirname);
	}

	public function logInDir($dirname = __DIR__) {
		$this->setBaseDirectory($dirname);
		$this->simpleLog();
	}

	public function simpleLog($logData, $type = "debug") {
		if (!\in_array($type, $this->supportedTypes)) {
			$type = "debug";
		}
		$title = "app.log";
		if ($this->fileExists($title)) {
			$this->createFile($title);
		}
		$date = new Date;
		$currentDate = $date->GetEnglishTimeStampFromTimeStamp($date->now());
		$data = $currentDate."  ".$logData;
	}

	public function newTrueLog($logData, $type = "debug", $file = "txt") {
		$title = $type.".".time().".";
		$this->createFile($title);
		$date = new Date;
		$currentDate = $date->GetEnglishTimeStampFromTimeStamp($date->now());
		$data = $currentDate."  ".$logData;
	}

	public function logToFile($filename, $logData, $type = "debug", $file = "txt") {
		if (!file_exists($this->getBaseDirectory()."/".$filename)) {
			$this->createFile($filename);
		}
		$date = new Date;
		$currentDate = $date->GetEnglishTimeStampFromTimeStamp($date->now());
		$data = $currentDate."  ".$logData;
		$this->appendFile($filename, $data);
	}
}