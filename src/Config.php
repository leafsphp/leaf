<?php

namespace Leaf;

/**
 * Leaf Config
 * -------------
 * Configure your leaf app
 */
class Config
{
    protected static $settings = [
        "app" => null,
        "app.down" => false,
        "mode" => "development",
        "debug" => true,
        "log.writer" => null,
        "log.level" => \Leaf\Log::DEBUG,
        "log.enabled" => true,
        "log.dir" => __DIR__ . "/../../../../storage/logs/",
        "log.file" => "log.txt",
        "log.open" => true,
        "http.version" => "1.1",
        // views
        "views.path" => null,
        "views.cachePath" => null,
    ];

    /**
     * Set configuration value(s)
     * 
     * @param string|array $item The config(s) to set
     * @param mixed $value The value for config. Ignored if $item is an array.
     */
    public static function set($item, $value = null)
    {
        if (is_string($item)) {
            static::$settings[$item] = $value;
        } else {
            static::$settings = array_merge(static::$settings, $item);
        }
    }

    /**
     * Get configuration
     * 
     * @param string|null $item The config to get. Returns all items if nothing is specified.
     */
    public static function get($item = null)
    {
        if ($item) {
            return static::$settings[$item];
        }

        return static::$settings;
    }
}
