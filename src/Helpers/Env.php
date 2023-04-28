<?php

namespace Leaf\Helpers;

/**
 * Leaf Env
 * -------------
 * A simple env solution for your leaf apps
 */
class Env
{
    public static function loadEnv(string $path)
    {
        if (!file_exists($path)) {
            throw new \Exception("Env file not found at $path");
        }

        $env = file_get_contents($path);
        $env = explode("\n", $env);

        foreach ($env as $key => $value) {
            if (strpos($value, "=") !== false) {
                $value = explode("=", $value);
                $envName = trim($value[0]);
                $envValue = trim($value[1], "\"' \t\n\r\0\x0B");

                static::set($envName, $envValue);
            }
        }
    }

    public static function set(string $name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                static::set($key, $value);
            }

            return;
        }

        if (is_string($name) && !empty($name)) {
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
            putenv("$name=$value");
        }
    }
}
