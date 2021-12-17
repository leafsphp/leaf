<?php

namespace Leaf\Auth;

/**
 * Leaf Simple Auth Sessions
 * -------------------------
 * Auth sessions made easy.
 *
 * @author Michael Darko
 * @since 3.0
 * @version 2.0.0
 */
class Session extends Core
{
    /**
     * Manually start an auth session
     */
    public static function init()
    {
        static::$session = new \Leaf\Http\Session(false);
        static::config("USE_SESSION", true);

        session_start();

        if (!static::$session->get("SESSION_STARTED_AT")) {
            static::$session->set("SESSION_STARTED_AT", time());
        }

        static::$session->set("SESSION_LAST_ACTIVITY", time());
    }

    /**
     * Session Length
     */
    public static function length()
    {
        static::experimental("length");

        return time() - static::$session->get("SESSION_STARTED_AT");
    }

    /**
     * Session last active
     */
    public static function lastActive()
    {
        static::experimental("lastActive");

        return time() - static::$session->get("SESSION_LAST_ACTIVITY");
    }

    /**
     * Refresh session
     */
    public static function refresh($clearData = true)
    {
        static::experimental("refresh");

        $success = static::$session->regenerate($clearData);

        static::$session->set("SESSION_STARTED_AT", time());
        static::$session->set("SESSION_LAST_ACTIVITY", time());
        static::$session->set("AUTH_SESISON", true);

        return $success;
    }

    /**
     * Define/Return session middleware
     *
     * **This method only works with session auth**
     */
    public static function middleware(string $name, callable $handler = null)
    {
        static::experimental("middleware");

        if (!$handler) return static::$middleware[$name];

        static::$middleware[$name] = $handler;
    }

    /**
     * Check session status
     */
    public static function status()
    {
        static::experimental("status");

        return static::$session->get("AUTH_USER") ?? false;
    }

    /**
     * End a session
     */
    public static function end($location = null)
    {
        static::experimental("end");

        static::$session->destroy();

        if ($location) {
            $route = static::config($location) ?? $location;
            \Leaf\Http\Response::redirect($route);
        }
    }

    /**
     * A simple auth guard: 'guest' pages can't be viewed when logged in,
     * 'auth' pages can't be viewed without authentication
     *
     * @param array|string $type The type of guard/guard options
     */
    public static function guard($type)
    {
        static::experimental("guard");

        if (is_array($type)) {
            if (isset($type["hasAuth"])) {
                $type = $type["hasAuth"] ? 'auth' : 'guest';
            }
        }

        if ($type === 'guest' && static::status()) {
            exit(header("location: " . static::config("GUARD_HOME"), true, 302));
        }

        if ($type === 'auth' && !static::status()) {
            exit(header("location: " . static::config("GUARD_LOGIN"), true, 302));
        }
    }

    /**
     * Save some data to auth session
     */
    public static function save($key, $data = null)
    {
        static::experimental("save");

        static::$session->set($key, $data);
    }
}
