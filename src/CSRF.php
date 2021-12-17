<?php

namespace Leaf\Anchor;

use Leaf\Anchor;
use Leaf\Http\Request;
use Leaf\Http\Session;

/**
 * CSRF handler
 */
class CSRF extends Anchor
{
    const TOKEN_NOT_FOUND = 'Token not found.';
    const TOKEN_INVALID = 'Invalid token.';

    /**
     * Manage config for leaf anchor
     * 
     * @param array|null $config The config to set
     */
    public static function config($config = null)
    {
        if ($config === null) return static::$config;

        static::$config = array_merge(static::$config, $config);
    }

    public static function init()
    {
        session_start();

        if (!isset($_SESSION[static::$config["SECRET_KEY"]])) {
            Session::set(static::$config["SECRET_KEY"], static::generateToken());
        }
    }

    public static function verify(): bool
    {
        if (in_array(Request::getPathInfo(), static::$config['EXCEPT'])) {
            return true;
        }

        if (in_array(Request::getMethod(), static::$config["METHODS"])) {
            $requestData = Request::body();
            $requestToken = $requestData[static::$config["SECRET_KEY"]] ?? null;

            if (!$requestToken) {
                static::$errors["token"] = static::TOKEN_NOT_FOUND;
                return false;
            }

            if ($requestToken !== $_SESSION[static::$config["SECRET_KEY"]]) {
                static::$errors["token"] = static::TOKEN_INVALID;
                return false;
            }
        }

        return true;
    }

    public static function token(): array
    {
        return [static::$config["SECRET_KEY"] => $_SESSION[static::$config["SECRET_KEY"]]];
    }

    public static function form()
    {
        echo '<input type="hidden" name="' . static::$config["SECRET_KEY"] . '" value="' . $_SESSION[static::$config["SECRET_KEY"]] . '" />';
    }
}
