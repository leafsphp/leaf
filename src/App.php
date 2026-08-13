<?php

declare(strict_types=1);

namespace Leaf;

/**
 * Leaf PHP Framework
 * --------
 * The easiest way to build simple but powerful apps and APIs quickly.
 *
 * @author Michael Darko <mickdd22@gmail.com>
 * @copyright 2019-2026 Michael Darko
 * @link https://leafphp.dev
 * @license MIT
 * @package Leaf
 */
class App extends Router
{
    /**
     * Callable to be invoked on application error
     * @var \Leaf\Crash\Handler|null
     */
    protected static $errorHandler = null;

    /********************************************************************************
     * Instantiation and Configuration
     *******************************************************************************/

    /**
     * Constructor
     * @param array $userSettings Associative array of application settings
     */
    public function __construct(array $userSettings = [])
    {
        $this->setupErrorHandler();
        $this->loadConfig($userSettings);

        $scripts = Config::getStatic('scripts');

        if (!empty($scripts)) {
            foreach ($scripts as $script) {
                \call_user_func($script, $this);
            }

            $this->loadConfig();
        }
    }

    protected function loadConfig(array $userSettings = [])
    {
        if (!empty($userSettings)) {
            Config::set(array_merge($userSettings, [
                'mode' => _env('APP_ENV', $userSettings['mode'] ?? Config::getStatic('mode')),
            ]));
        }

        $this->setupDefaultContainer();
        $this->syncCrashContext();
    }

    /**
     * Keep the crash engine in step with app config: debug mode decides
     * the crash page vs the production page, and every report carries
     * the request + app context without any user setup.
     */
    protected function syncCrashContext()
    {
        if (!(static::$errorHandler instanceof \Leaf\Crash\Handler)) {
            return;
        }

        static::$errorHandler->debug(Anchor::toBool(Config::getStatic('debug')) ?? true);

        $context = [
            'appRoot' => getcwd(),
            'app' => [
                'env' => Config::getStatic('mode'),
                'leaf' => defined('static::VERSION') ? static::VERSION : 'v5',
            ],
        ];

        if (isset($_SERVER['REQUEST_METHOD'])) {
            $context['request'] = [
                'method' => $_SERVER['REQUEST_METHOD'],
                'url' => ($_SERVER['REQUEST_URI'] ?? '/'),
            ];
        }

        crash()->context($context);
    }

    protected function setupErrorHandler()
    {
        // registering once keeps custom handlers intact and the handler stack balanced
        if (static::$errorHandler !== null) {
            return;
        }

        static::$errorHandler = new \Leaf\Crash\Handler(crash());
        static::$errorHandler->register();
    }

    /**
     * Set a custom error screen.
     * @param $handler The function to be executed
     */
    public function setErrorHandler($handler)
    {
        if (Anchor::toBool(Config::getStatic('debug')) === false) {
            static::$errorHandler->renderWith(function ($report) use ($handler) {
                ob_start();
                $handler($report);

                return ob_get_clean();
            });
        }
    }

    /**
     * This method adds a method to the global leaf instance
     * Register a method and use it globally on the Leaf Object
     */
    public function register($name, $value)
    {
        Config::singleton($name, $value);
    }

    /**
     * Check if a dependency exists in the container
     * @param string $name The name of the registered dependency
     */
    public function has(string $name): bool
    {
        return Config::has($name);
    }

    private function setupDefaultContainer()
    {
        $mode = _env('APP_ENV', Config::getStatic('mode') ?: 'development');
        Config::set('mode', $mode);

        if (Config::getStatic('debug') === null) {
            Config::set('debug', $mode !== 'production');
        }

        $sessionCookie = Config::getStatic('session.cookie') ?? [];

        if (($sessionCookie['secure'] ?? null) === null) {
            $sessionCookie['secure'] = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
            Config::set('session.cookie', $sessionCookie);
        }

        Config::singleton('request', function () {
            return new Http\Request();
        });

        Config::singleton('response', function () {
            return new Http\Response();
        });

        Config::singleton('headers', function () {
            return new Http\Headers();
        });

        Config::singleton('app', function () {
            return $this;
        });
    }

    public function __get($name)
    {
        return Config::get($name);
    }

    public function __set($name, $value)
    {
        Config::set($name, $value);
    }

    public function __isset($name)
    {
        return Config::has($name);
    }

    public function __unset($name)
    {
        Config::remove($name);
    }

    public function __call($method, $args)
    {
        $view = Config::view($method);

        if ($view !== null) {
            return $view;
        }

        throw new \BadMethodCallException(
            "Call to undefined method Leaf\\App::$method(). If you're trying to use a view engine, attach it with attachView() first."
        );
    }

    /**
     * Configure Leaf Settings
     *
     * This method defines application settings and acts as a setter and a getter.
     *
     * If only one argument is specified and that argument is a string, the value
     * of the setting identified by the first argument will be returned, or NULL if
     * that setting does not exist.
     *
     * If only one argument is specified and that argument is an associative array,
     * the array will be merged into the existing application settings.
     *
     * If two arguments are provided, the first argument is the name of the setting
     * to be created or updated, and the second argument is the setting value.
     *
     * @param  string|array $name  If a string, the name of the setting to set or retrieve. Else an associated array of setting names and values
     * @param  mixed $value If name is a string, the value of the setting identified by $name
     * @return mixed The value of a setting if only one argument is a string
     */
    public function config($name, $value = null)
    {
        if ($value === null && \is_string($name)) {
            return Config::get($name);
        }

        Config::set($name, $value);
        $this->loadConfig();
        $this->setupErrorHandler();
    }

    /**
     * Run code that can change the behaviour of Leaf
     * *Usually used by library creators*
     */
    public function attach(callable $code)
    {
        \call_user_func($code, $this, Config::get());
        $this->loadConfig();
        $this->setupErrorHandler();
    }

    /**
     * Attach a view engine to Leaf
     * @param mixed $view The view engine to attach
     */
    public function attachView($view, $name = null)
    {
        Config::attachView($view, $name);
    }

    /**
     * Evade CORS errors
     * @param $options Config for cors
     */
    public function cors($options = [])
    {
        if (\class_exists('Leaf\Http\Cors')) {
            Http\Cors::config($options);
        } else {
            \trigger_error('Cors module not found! Run `leaf install cors` or `composer require leafs/cors` to install the CORS module. This is required to configure CORS.');
        }
    }

    /**
     * Tune vite to work without Leaf MVC
     */
    public function vite(
        $options = [
            'assets' => '',
            'build' => '',
            'hotFile' => 'hot',
        ]
    ) {
        if (class_exists('Leaf\Vite')) {
            \Leaf\Vite::config('assets', $options['assets']);
            \Leaf\Vite::config('build', $options['build']);
            \Leaf\Vite::config('hotFile', $options['hotFile']);
        }
    }

    /**
     * Add CSRF protection to your app
     *
     * @param array $options Config for csrf
     */
    public function csrf($options = [])
    {
        if (!\class_exists('Leaf\Anchor\CSRF')) {
            \trigger_error('CSRF module not found! Run `leaf install csrf` or `composer require leafs/csrf` to install the CSRF module. This is required to configure CSRF.');
        }

        Config::set('session', true);

        if (!Anchor\CSRF::token()) {
            Anchor\CSRF::config($options);
            Anchor\CSRF::init();
        }

        $this->use(function () {
            Anchor\CSRF::validate();
        });
    }

    /********************************************************************************
     * Logging
     *******************************************************************************/

    /**
     * Get application log
     *
     * @return \Leaf\Log|null|void
     */
    public function logger()
    {
        if (!$this->log) {
            \trigger_error('You need to enable logging to use this feature! Set log.enabled to true and install the logger module');
        }

        return $this->log;
    }

    /********************************************************************************
     * Application Accessors
     *******************************************************************************/

    /**
     * Get the Request Headers
     */
    public function headers(): Http\Headers
    {
        return $this->headers;
    }

    /**
     * Get the Request object
     */
    public function request(): Http\Request
    {
        return $this->request;
    }

    /**
     * Get the Response object
     */
    public function response(): Http\Response
    {
        return $this->response;
    }

    /********************************************************************************
     * Helper Methods
     *******************************************************************************/

    /**
     * Get the absolute path to this Leaf application's root directory
     *
     * This method returns the absolute path to the Leaf application's
     * directory. If the Leaf application is installed in a public-accessible
     * sub-directory, the sub-directory path will be included. This method
     * will always return an absolute path WITH a trailing slash.
     *
     * @return string
     */
    public function root()
    {
        return \rtrim($_SERVER['DOCUMENT_ROOT'], '/') . \rtrim($this->request->getScriptName(), '/') . '/';
    }

    /**
     * Halt
     *
     * Stop the application and immediately send the response with a
     * specific status and body to the HTTP client. This may send any
     * type of response: info, success, redirect, client error, or server error.
     *
     * @param int $status The HTTP response status
     * @param string $message The HTTP response body
     */
    public static function halt($status, $message = '')
    {
        if (\ob_get_level() !== 0) {
            \ob_clean();
        }

        Http\Headers::resetStatus($status);
        response()->exit($message, $status);
    }

    /********************************************************************************
     * Env, router and server
     *******************************************************************************/

    /**
     * Create mode-specific code
     *
     * @param string $mode The mode to run code in
     * @param callable $callback The code to run in selected mode.
     */
    public static function script($mode, $callback)
    {
        static::hook('router.before', function () use ($mode, $callback) {
            $appMode = Config::getStatic('mode') ?? 'development';

            if ($mode === $appMode) {
                return $callback();
            }
        });
    }

    /**
     * Run mode-specific code. Unlike script, this runs immediately.
     *
     * @param string $mode The mode to run code in
     * @param callable $callback The code to run in selected mode.
     */
    public static function environment($mode, $callback)
    {
        $appMode = Config::getStatic('mode') ?? 'development';

        if ($mode === $appMode) {
            return $callback();
        }
    }

    /**
     * @inheritdoc
     */
    public static function run(?callable $callback = null)
    {
        return parent::run($callback);
    }
}
