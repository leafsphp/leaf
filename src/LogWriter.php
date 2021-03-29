<?php

namespace Leaf;

/**
 * Log Writer
 *
 * This class is used by Leaf_Log to write log messages to a valid, writable
 * resource handle (e.g. a file or STDERR).
 *
 * @package Leaf
 * @author  Michael Darko
 * @since   2.0.0
 */
class LogWriter
{
    protected $logFile;

    /**
     * Constructor
     * @param string $file File to log to
     * @param bool $createFile Create file if it's not found
     */
    public function __construct(string $file, bool $createFile = false)
    {
        if (!file_exists($file)) {
            if ($createFile) {
                FS::createFile($file);
            } else {
                trigger_error(basename($file) . " not found in " . dirname($file), E_USER_ERROR);
            }
        }

        $this->logFile = $file;
    }

    /**
     * Write message
     * 
     * @param mixed $message
     * @param int $level
     * @return int|bool
     */
    public function write($message, $level = null)
    {
        if ($level !== null) {
            $level = Log::getLevel($level) . " - ";
        }

        FS::prepend(
            $this->logFile,
            (string) "[" . Date::now() . "]\n" . $level . "$message\n"
        );
        return 1;
    }
}
