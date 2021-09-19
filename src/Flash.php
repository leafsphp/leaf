<?php

namespace Leaf;

/**
 * Leaf Flash
 * -----
 * Simple flash messages for your leaf apps
 * 
 * @author Michael Darko <mickdd22@gmail.com>
 * @since 2.5.0
 */
class Flash
{
    private static $config = [
        "key" => "leaf.flash",
        "default" => "message",
        "saved" => "leaf.flash.saved",
    ];

    /**
     * Configure leaf flash
     * 
     * @param array $config Configuration for leaf flash
     */
    public static function config(array $config)
    {
        static::$config = array_merge(static::$config, $config);
    }

    /**
     * Set a new flash message
     * 
     * @param string $message The flash message to set
     * @param string $key The key to save message
     */
    public static function set(string $message, string $key = "default")
    {
        static::session();

        if ($key === "default") {
            $key = static::$config["default"];
        }

        $_SESSION[static::$config["key"]][$key] = $message;
    }

    /**
     * Remove a flash message
     * 
     * @param string|null $key The key of message to remove
     */
    public static function unset(string $key = null)
    {
        static::session();

        if (!$key) {
            Http\Session::unset(static::$config["key"]);
        } else {
            if ($key === "default") {
                $key = static::$config["default"];
            }

            $_SESSION[static::$config["key"]][$key] = null;
        }
    }

    /**
     * Get the flash array
     * 
     * @param string|null $key The key of message to get
     * @return string|array
     */
    private static function get(string $key = null)
    {
        static::session();

        if (!$key) {
            return Http\Session::get(static::$config["key"]);
        }

        if ($key === "default") {
            $key = static::$config["default"];
        }

        $item = null;
        $items = Http\Session::get(static::$config["key"], false);

        if (isset($items[$key])) {
            $item = $items[$key];
        }

        if ($key) {
            static::unset($key);
        }

        return $item;
    }

    /**
     * Display a flash message
     * 
     * @param string $key The key of message to display
     * @return string
     */
    public static function display(string $key = "default")
    {
        static::session();

        return static::get($key);
    }

    /**
     * Save a flash message (won't delete after view).
     * You can save only one message at a time.
     * 
     * @param string $message The flash message to save
     */
    public static function save(string $message)
    {
        static::session();

        Http\Session::set(static::$config["saved"], $message);
    }

    /**
     * Clear the saved flash message
     */
    public static function clearSaved()
    {
        static::session();

        Http\Session::set(static::$config["saved"], null);
    }

    /**
     * Display the saved flash message
     */
    public static function displaySaved()
    {
        static::session();

        return Http\Session::get(static::$config["saved"]);
    }

    private static function session()
    {
        if (!session_id()) {
            session_start();
        }
    }
}
