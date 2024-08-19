<?php

declare(strict_types=1);

if (!function_exists('app')) {
    /**
     * Return the Leaf instance
     *
     */
    function app(): Leaf\App
    {
        if (!(\Leaf\Config::getStatic('app'))) {
            \Leaf\Config::singleton('app', function () {
                return new \Leaf\App();
            });
        }

        return \Leaf\Config::get('app');
    }
}

if (!function_exists('_env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function _env($key, $default = null)
    {
        $env = array_merge(getenv() ?? [], $_ENV ?? []);

        return $env[$key] ??= $default;
    }
}
