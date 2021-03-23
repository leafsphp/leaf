<?php

namespace Leaf;

/**
 * Leaf View Engine
 * -----------
 * View engine manager for Leaf.
 *
 * @author Michael Darko <mickdd22@gmail.com>
 * @since v2.4.4
 */
class View
{
    public static $engines = [];

    /**
     * Attach view engine to Leaf view
     * 
     * @param mixed $className The class to attach
     * @param string|null $name The key to save view engine with
     */
    public static function attach($className, $name = null)
    {
        $class = new $className;
        static::$engines[$name ?? static::getDiIndex($class)] = $class;
    }

    private static function getDiIndex($class)
    {
        $className = strtolower(get_class($class));

        $fullName = explode("\\", $className);
        $className = $fullName[count($fullName) - 1];

        return $className;
    }

    public static function __callstatic($name, $arguments)
    {
        return static::$engines[$name];
    }
}
