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
    /**
     * @var resource
     */
    protected $resource;

    /**
     * Constructor
     * @param  resource                  $resource
     * @throws \InvalidArgumentException If invalid resource
     */
    public function __construct($resource)
    {
        if (!is_resource($resource)) {
            throw new \InvalidArgumentException('Cannot create LogWriter. Invalid resource handle.');
        }
        $this->resource = $resource;
    }

    /**
     * Write message
     * @param  mixed     $message
     * @param  int       $level
     * @return int|bool
     */
    public function write($message, $level = null)
    {
        return fwrite($this->resource, (string) $message . PHP_EOL);
    }
}
