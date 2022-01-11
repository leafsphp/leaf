<?php

declare(strict_types=1);

if (!function_exists('app')) {
    /**
     * Return the Leaf instance
     *
     * @return Leaf\App
     */
    function app()
    {
        $app = Leaf\Config::get("app")["instance"] ?? null;

        if (!$app) {
            $app = new Leaf\App();
            Leaf\Config::set("app", ["instance" => $app]);
        }

        return $app;
    }
}

if (!function_exists('_env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function _env($key, $default = null)
    {
        $item = getenv($key);

        if (!isset($_ENV[$key]) || (isset($_ENV[$key]) && $_ENV[$key] == null)) {
            $item = $default;
        }

        return $item;
    }
}
