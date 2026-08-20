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
        static $env;

        if ($env === null) {
            $env = array_merge(getenv() ?: [], $_ENV ?? []);
        }

        if (!array_key_exists($key, $env)) {
            return $default;
        }

        return _envCast($env[$key], $default);
    }
}

if (!function_exists('_envUncached')) {
    /**
     * Read an environment variable live, skipping _env()'s per-request
     * cache. Every call hits the environment, so it is dramatically
     * slower than _env() — only reach for this when something mutates
     * the environment mid-request (eg. putenv()) and you need to see it.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function _envUncached($key, $default = null)
    {
        $value = $_ENV[$key] ?? getenv($key);

        if ($value === false || $value === null) {
            return $default;
        }

        return _envCast($value, $default);
    }
}

if (!function_exists('_envCast')) {
    /**
     * Normalize a raw environment string the way _env() does —
     * true/false/empty/null keywords and quoted values.
     *
     * @param  mixed  $value
     * @param  mixed  $default
     * @return mixed
     */
    function _envCast($value, $default = null)
    {
        if ($value === null) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;

            case 'false':
            case '(false)':
                return false;

            case 'empty':
            case '(empty)':
                return '';

            case 'null':
            case '(null)':
                return;
        }

        if (str_starts_with($value, '"') && str_ends_with($value, '"') && strlen($value) >= 2) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}

if (!function_exists('make')) {
    /**
     * Cache and use a class
     *
     * @template T of object
     * @param class-string<T>|T $service
     * @return T
     */
    function make($service)
    {
        if (is_string($service)) {
            $serviceName = $service;
            $service = (new $service());
        } else {
            $serviceName = get_class($service);
        }

        if (!\Leaf\Config::getStatic("classes.$serviceName")) {
            \Leaf\Config::singleton("classes.$serviceName", function () use ($service) {
                return $service;
            });
        }

        return \Leaf\Config::get("classes.$serviceName");
    }
}

if (!function_exists('rescue')) {
    /**
     * Run the given callback and return its result. If an exception occurs, report it and return the default value.
     *
     * @template T
     * @param  callable  $callback
     * @param  mixed  $default
     * @param  bool  $report Report the exception to Leaf Crash
     * @return T|mixed
     */
    function rescue(callable $callback, $default = null, bool $report = true)
    {
        try {
            return $callback();
        } catch (Throwable $e) {
            if ($report && function_exists('crash')) {
                // a rescued exception is handled, not fatal, but it still
                // belongs in the journey of whatever fails later
                crash()->leaveCrumb(
                    'rescued ' . get_class($e) . ': ' . $e->getMessage(),
                    \Leaf\Crash\Breadcrumbs::TYPE_ACTION,
                    ['file' => $e->getFile() . ':' . $e->getLine()]
                );

                crash()->capture($e, ['level' => 'warning']);
            }

            return $default instanceof Closure ? $default($e) : $default;
        }
    }
}
