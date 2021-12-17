<?php

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

if (!function_exists('_token')) {
    /**
     * Return CSRF token
     */
    function _token()
    {
        return \Leaf\Anchor\CSRF::token();
    }
}

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
            $app = new Leaf\App;
            Leaf\Config::set("app", ["instance" => $app]);
        }

        return $app;
    }
}

if (!function_exists('auth')) {
    /**
     * Return Leaf's auth object or run an auth guard
     * 
     * @param string|null $guard The auth guard to run
     */
    function auth(string $guard = null)
    {
        if (!$guard) {
            if (class_exists('\Leaf\Config')) {
                $auth = Leaf\Config::get("auth")["instance"] ?? null;

                if (!$auth) {
                    $auth = new Leaf\Auth;
                    Leaf\Config::set("auth", ["instance" => $auth]);
                }

                return $auth;
            }

            return \Leaf\Auth::class;
        }

        if ($guard === 'session') {
            return \Leaf\Auth::session();
        }

        return \Leaf\Auth::guard($guard);
    }
}

if (!function_exists('cookie')) {
    /**
     * Return cookie data/object or set cookie data
     *
     * @param mixed $key — The data to set
     * @param null $value
     * @return \Leaf\Http\Cookie|mixed|void|null
     */
    function cookie($key = null, $value = null)
    {
        if (!$key && !$value) {
            return new \Leaf\Http\Cookie();
        }

        if (!$value && is_string($key)) {
            return \Leaf\Http\Cookie::get($key);
        }

        \Leaf\Http\Cookie::set($key, $value);
    }
}

if (!function_exists('flash')) {
    /**
     * Return flash data/object or set flash data
     *
     * @param string|null $key — The flash data to set/get
     * @param mixed $key — The data to set
     */
    function flash($key = null, $value = null)
    {
        if (!$key && !$value) {
            return new \Leaf\Flash();
        }

        if (!$value && is_string($key)) {
            return \Leaf\Flash::display($key);
        }

        return \Leaf\Flash::set($key, $value);
    }
}

if (!function_exists('hasAuth')) {
    /**
     * Find out if there's an active sesion
     */
    function hasAuth(): bool
    {
        return !!sessionUser();
    }
}

if (!function_exists('request')) {
    /**
     * Return request or request data
     *
     * @param array|string $data — Get data from request
     */
    function request($data = null)
    {
        if ($data !== null) return \Leaf\Http\Request::get($data);
        return new \Leaf\Http\Request();
    }
}

if (!function_exists('response')) {
    /**
     * Return response or set response data
     *
     * @param array|string $data — The JSON response to set
     */
    function response($data = null)
    {
        if ($data !== null) return \Leaf\Http\Response::json($data);
        return new \Leaf\Http\Response();
    }
}

if (!function_exists('session')) {
    /**
     * Return session data/object or set session data
     *
     * @param string|null $key — The session data to set/get
     * @param mixed $key — The data to set
     */
    function session($key = null, $value = null)
    {
        if (!$key && !$value) {
            return new \Leaf\Http\Session();
        }

        if (!$value && ($key && is_string($key))) {
            return \Leaf\Http\Session::get($key);
        }

        if (!$value && ($key && is_array($key))) {
            return \Leaf\Http\Session::set($key);
        }

        return \Leaf\Http\Session::set($key, $value);
    }
}

if (!function_exists('sessionUser')) {
    /**
     * Get the currently logged in user
     */
    function sessionUser()
    {
        return \Leaf\Http\Session::get('AUTH_USER');
    }
}
