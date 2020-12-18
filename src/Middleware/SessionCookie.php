<?php

namespace Leaf\Middleware;

/**
 * Session Cookie
 *
 * This class provides an HTTP cookie storage mechanism
 * for session data. This class avoids using a PHP session
 * and instead serializes/unserializes the $_SESSION global
 * variable to/from an HTTP cookie.
 *
 * You should NEVER store sensitive data in a client-side cookie
 * in any format, encrypted (with cookies.encrypt) or not. If you
 * need to store sensitive user information in a session, you should
 * rely on PHP's native session implementation, or use other middleware
 * to store session data in a database or alternative server-side cache.
 *
 * Because this class stores serialized session data in an HTTP cookie,
 * you are inherently limited to 4 Kb. If you attempt to store
 * more than this amount, serialization will fail.
 *
 * @package     Leaf
 * @author     Michael Darko
 * @since      2.0.0
 */
class SessionCookie extends \Leaf\Middleware
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * Constructor
     *
     * @param array $settings
     */
    public function __construct($settings = array())
    {
        $defaults = array(
            'expires' => '20 minutes',
            'path' => '/',
            'domain' => null,
            'secure' => false,
            'httponly' => false,
            'name' => 'leaf_session',
        );
        $this->settings = array_merge($defaults, $settings);
        if (is_string($this->settings['expires'])) {
            $this->settings['expires'] = strtotime($this->settings['expires']);
        }

        /**
         * Session
         *
         * We must start a native PHP session to initialize the $_SESSION superglobal.
         * However, we won't be using the native session store for persistence, so we
         * disable the session cookie and cache limiter. We also set the session
         * handler to this class instance to avoid PHP's native session file locking.
         */
        ini_set('session.use_cookies', 0);
        session_cache_limiter(false);
        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'gc')
        );
    }

    /**
     * Call
     */
    public function call()
    {
        $this->loadSession();
        $this->next->call();
        $this->saveSession();
    }

    /**
     * Load session
     */
    protected function loadSession()
    {
        if (session_id() === '') {
            session_start();
        }
        $value = $this->app->getCookie($this->settings['name']);
        if ($value) {
            $value = json_decode($value, true);
            $_SESSION = is_array($value) ? $value : array();
        } else {
            $_SESSION = array();
        }
    }

    /**
     * Save session
     */
    protected function saveSession()
    {
        $value = json_encode($_SESSION);

        if (strlen($value) > 4096) {
            $this->app->getLog()->error('WARNING! Leaf\Middleware\SessionCookie data size is larger than 4KB. Content save failed.');
        } else {
            $this->app->setCookie(
                $this->settings['name'],
                $value,
                $this->settings['expires'],
                $this->settings['path'],
                $this->settings['domain'],
                $this->settings['secure'],
                $this->settings['httponly']
            );
        }
        // session_destroy();
    }

    /********************************************************************************
     * Session Handler
     *******************************************************************************/

    /**
     * @codeCoverageIgnore
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * @codeCoverageIgnore
     */
    public function close()
    {
        return true;
    }

    /**
     * @codeCoverageIgnore
     */
    public function read($id)
    {
        return '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function write($id, $data)
    {
        return true;
    }

    /**
     * @codeCoverageIgnore
     */
    public function destroy($id)
    {
        return true;
    }

    /**
     * @codeCoverageIgnore
     */
    public function gc($maxlifetime)
    {
        return true;
    }
}
