<?php

declare(strict_types=1);

namespace Leaf\Auth;
/**
 * Auth Sessions [CORE]
 * -----
 * Core engine powering auth sessions.
 *
 * @author Michael Darko
 * @since 1.5.0
 * @version 2.0.0
 */
class Session
{
    /**
     * @var \Leaf\Http\Session
     */
    protected static $session;

    public static function init()
    {
        static::$session = new \Leaf\Http\Session(false);

        if (!isset($_SESSION)) {
            session_start();
        };

        if (!static::$session->get("SESSION_STARTED_AT")) {
            static::$session->set("SESSION_STARTED_AT", time());
        }

        static::$session->set("SESSION_LAST_ACTIVITY", time());

        return static::$session;
    }
}
