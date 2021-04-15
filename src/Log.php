<?php

namespace Leaf;

/**
 * Log
 *
 * This is the primary logger for a Leaf application. You may provide
 * a Log Writer in conjunction with this Log to write to various output
 * destinations (e.g. a file). This class provides this interface:
 *
 * debug( mixed $object, array $context )
 * info( mixed $object, array $context )
 * notice( mixed $object, array $context )
 * warning( mixed $object, array $context )
 * error( mixed $object, array $context )
 * critical( mixed $object, array $context )
 * alert( mixed $object, array $context )
 * emergency( mixed $object, array $context )
 * log( mixed $level, mixed $object, array $context )
 *
 * This class assumes only that your Log Writer has a public `write()` method
 * that accepts any object as its one and only argument. The Log Writer
 * class may write or send its argument anywhere: a file, STDERR,
 * a remote web API, etc. The possibilities are endless.
 *
 * @package Leaf
 * @author  Michael Darko
 * @since   1.5.0
 */
class Log
{
    const EMERGENCY = 1;
    const ALERT     = 2;
    const CRITICAL  = 3;
    const ERROR     = 4;
    const WARN      = 5;
    const NOTICE    = 6;
    const INFO      = 7;
    const DEBUG     = 8;

    /**
     * @var array
     */
    protected static $levels = [
        self::EMERGENCY => 'EMERGENCY',
        self::ALERT     => 'ALERT',
        self::CRITICAL  => 'CRITICAL',
        self::ERROR     => 'ERROR',
        self::WARN      => 'WARNING',
        self::NOTICE    => 'NOTICE',
        self::INFO      => 'INFO',
        self::DEBUG     => 'DEBUG'
    ];

    /**
     * @var mixed
     */
    protected $writer;

    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @var int
     */
    protected $level;

    /**
     * Constructor
     * 
     * @param  mixed $writer
     */
    public function __construct($writer)
    {
        $this->writer = $writer;
        $this->enabled = true;
        $this->level = self::DEBUG;
    }

    /**
     * Enable or disable logging/return logging state
     * 
     * @param bool|null $enabled Whether to enable or disable logging
     * @return bool|void
     */
    public function enabled(?bool $enabled = null)
    {
        if ($enabled === null) {
            return $this->enabled;
        }

        $this->enabled = $enabled;
    }

    /**
     * Get/Set log level
     * 
     * @param int|null $level The log level
     */
    public function level(?int $level = null)
    {
        if ($level === null) {
            return $this->level;
        }

        if (!isset(self::$levels[$level])) {
            trigger_error("Invalid log level: " . self::$levels[$level]);
        }

        $this->level = $level;
    }

    /**
     * Get/Set log level
     * 
     * @param int|null $level The log level
     */
    public static function getLevel(int $level)
    {
        return static::$levels[$level];
    }

    /**
     * Get/Set log writer
     * 
     * @param mixed $writer
     */
    public function writer($writer = null)
    {
        if ($writer === null) {
            return $this->writer;
        }

        $this->writer = $writer;
    }

    /**
     * Is logging enabled?
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Log debug message
     * @param mixed $object
     * @param array $context
     * @return mixed|bool What the Logger returns, or false if Logger not set or not enabled
     */
    public function debug($object, array $context = [])
    {
        return $this->log(self::DEBUG, $object, $context);
    }

    /**
     * Log info message
     * @param mixed $object
     * @param array $context
     * @return mixed|bool What the Logger returns, or false if Logger not set or not enabled
     */
    public function info($object, array $context = [])
    {
        return $this->log(self::INFO, $object, $context);
    }

    /**
     * Log notice message
     * @param mixed $object
     * @param array $context
     * @return mixed|bool What the Logger returns, or false if Logger not set or not enabled
     */
    public function notice($object, array $context = [])
    {
        return $this->log(self::NOTICE, $object, $context);
    }

    /**
     * Log warning message
     * @param mixed $object
     * @param array $context
     * @return mixed|bool What the Logger returns, or false if Logger not set or not enabled
     */
    public function warning($object, array $context = [])
    {
        return $this->log(self::WARN, $object, $context);
    }

    /**
     * Log error message
     * @param mixed $object
     * @param array $context
     * @return mixed|bool What the Logger returns, or false if Logger not set or not enabled
     */
    public function error($object, array $context = [])
    {
        return $this->log(self::ERROR, $object, $context);
    }

    /**
     * Log critical message
     * @param mixed $object
     * @param array $context
     * @return mixed|bool What the Logger returns, or false if Logger not set or not enabled
     */
    public function critical($object, array $context = [])
    {
        return $this->log(self::CRITICAL, $object, $context);
    }

    /**
     * Log alert message
     * @param mixed $object
     * @param array $context
     * @return mixed|bool What the Logger returns, or false if Logger not set or not enabled
     */
    public function alert($object, array $context = [])
    {
        return $this->log(self::ALERT, $object, $context);
    }

    /**
     * Log emergency message
     * @param mixed $object
     * @param array $context
     * @return mixed|bool What the Logger returns, or false if Logger not set or not enabled
     */
    public function emergency($object, array $context = [])
    {
        return $this->log(self::EMERGENCY, $object, $context);
    }

    /**
     * Log message
     * @param mixed $level
     * @param mixed $object
     * @param array $context
     * 
     * @return mixed|bool What the Logger returns, or false if Logger not set or not enabled
     * @throws \InvalidArgumentException If invalid log level
     */
    public function log($level, $object, array $context = [])
    {
        if (!isset(self::$levels[$level])) {
            trigger_error('Invalid log level supplied to function');
        } else if ($this->enabled && $this->writer && $level <= $this->level) {
            if (is_array($object) || (is_object($object) && !method_exists($object, "__toString"))) {
                $message = print_r($object, true);
            } else {
                $message = (string) $object;
            }

            if (count($context) > 0) {
                if (isset($context['exception']) && $context['exception'] instanceof \Exception) {
                    $message .= ' - ' . $context['exception'];
                    unset($context['exception']);
                }

                $message = $this->interpolate($message, $context);
            }

            return $this->writer->write($message, $level);
        } else {
            return false;
        }
    }

    /**
     * Interpolate log message
     * @param mixed $message   The log message
     * @param array $context   An array of placeholder values
     * @return string The processed string
     */
    protected function interpolate($message, array $context = [])
    {
        $replace = [];

        foreach ($context as $key => $value) {
            $replace['{' . $key . '}'] = $value;
        }

        return strtr($message, $replace);
    }
}
