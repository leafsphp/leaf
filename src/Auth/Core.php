<?php

namespace Leaf\Auth;

use Leaf\Db;
use Leaf\Form;

/**
 * Leaf Simple Auth [Core]
 * -------------------------
 * Authentication made easy.
 *
 * @author Michael Darko
 * @since 3.0
 * @version 2.0.0
 */
class Core
{
    /**
     * All errors caught
     */
    protected static $errorsArray = [];

    /**
     * @var \Leaf\Http\Session
     */
    protected static $session;

    /**
     * All defined session middleware
     */
    protected static $middleware = [];

    /**
     * Auth Settings
     */
    protected static $settings = [
        "AUTH_NO_PASS" => false,
        "USE_TIMESTAMPS" => true,
        "PASSWORD_ENCODE" => null,
        "PASSWORD_VERIFY" => null,
        "PASSWORD_KEY" => "password",
        "HIDE_ID" => true,
        "ID_KEY" => "id",
        "USE_UUID" => false,
        "HIDE_PASSWORD" => true,
        "LOGIN_PARAMS_ERROR" => "Incorrect credentials!",
        "LOGIN_PASSWORD_ERROR" => "Password is incorrect!",
        "USE_SESSION" => false,
        "SESSION_ON_REGISTER" => false,
        "GUARD_LOGIN" => "/auth/login",
        "GUARD_REGISTER" => "/auth/register",
        "GUARD_HOME" => "/home",
        "GUARD_LOGOUT" => "/auth/logout",
        "SAVE_SESSION_JWT" => false,
        "TOKEN_LIFETIME" => null,
        "TOKEN_SECRET" => "@_leaf$0Secret!"
    ];

    /**
     * @var \Leaf\Db
     */
    public static $db;

    /**
     * @var \Leaf\Form
     */
    public static $form;

    public function __construct($useSession = false)
    {
        static::$form = new Form;
        static::$db = new Db();

        if ($useSession) {
            static::$useSession();
        }
    }

    /**
     * Create a db connection
     *
     * @param string $host The db name
     * @param string $user
     * @param string $password
     * @param string $dbname
     */
    public static function connect(string $host, string $user, string $password, string $dbname): void
    {
        static::$form = new Form;
        static::$db = new Db;

        static::$db->connect($host, $user, $password, $dbname);
    }

    /**
     * Create a database connection from env variables
     */
    public static function autoConnect(): void
    {
        static::connect(
            getenv("DB_HOST"),
            getenv("DB_USERNAME"),
            getenv("DB_PASSWORD"),
            getenv("DB_DATABASE")
        );
    }

    /**
     * Set auth config
     */
    public static function config($config, $value = null)
    {
        if (is_array($config)) {
            foreach ($config as $key => $configValue) {
                static::config($key, $configValue);
            }
        } else {
            if (!$value) return static::$settings[$config] ?? null;
            static::$settings[$config] = $value;
        }
    }

    /**
     * Exception for experimental features
     */
    protected static function experimental($method)
    {
        if (!static::config("USE_SESSION")) {
            trigger_error("Auth::$method is experimental. Turn on USE_SESSION to use this feature.");
        }
    }

    /**
     * Get all authentication errors as associative array
     */
    public static function errors(): array
    {
        return static::$errorsArray;
    }
}
