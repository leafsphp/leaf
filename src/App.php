<?php

declare(strict_types=1);

namespace Leaf;

/**
 * Leaf PHP Framework
 * --------
 * The easiest way to build simple but powerful apps and APIs quickly.
 *
 * @author Michael Darko <mickdd22@gmail.com>
 * @copyright 2019-2022 Michael Darko
 * @link https://leafphp.dev
 * @license MIT
 * @package Leaf
 */
class App extends Router
{
    /**
     * Leaf container instance
     * @var \Leaf\Helpers\Container
     */
    protected $container;

    /**
     * Callable to be invoked on application error
     */
    protected $errorHandler;

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
        $this->container = new \Leaf\Helpers\Container();
        $this->loadConfig($userSettings);

        if (!empty($this->config('scripts'))) {
            foreach ($this->config('scripts') as $script) {
                call_user_func($script, $this, \Leaf\Config::get());
            }

            $this->loadConfig();
        }
    }

    protected function loadConfig(array $userSettings = [])
    {
        if (!empty($userSettings)) {
            Config::set($userSettings);
        }

        $this->setupDefaultContainer();
        $this->loadViewEngines();
    }

    protected function setupErrorHandler()
    {
        $this->errorHandler = (new \Leaf\Exception\Run());
        $this->errorHandler->register();

        error_reporting((bool) $this->config('debug') ? E_ALL : 0);
        ini_set('display_errors', (bool) $this->config('debug') ? '1' : '0');
    }

    /**
     * Set a custom error screen.
     * @param callable|array $handler The function to be executed
     */
    public function setErrorHandler($handler, bool $wrapper = true)
    {
        if (Anchor::toBool($this->config('debug')) === false) {
            if ($this->errorHandler instanceof \Leaf\Exception\Run) {
                $this->errorHandler->unregister();
            }


            $this->errorHandler = new \Leaf\Exception\Run();

            if ($handler instanceof \Leaf\Exception\Handler\Handler) {
                $this->errorHandler->pushHandler($handler)->register();
            } else {
                $this
                    ->errorHandler
                    ->pushHandler(new \Leaf\Exception\Handler\CustomHandler($handler))
                    ->register();
            }
        }
    }

    /**
     * This method adds a method to the global leaf instance
     * Register a method and use it globally on the Leaf Object
     */
    public function register($name, $value)
    {
        $this->container->singleton($name, $value);
    }

    /**
     * This method loads all added view engines
     */
    public function loadViewEngines()
    {
        $views = View::$engines;

        if (!empty($views)) {
            foreach ($views as $key => $value) {
                $this->container->singleton($key, function () use ($value) {
                    return $value;
                });
            }
        }
    }

    private function setupDefaultContainer()
    {
        $this->container->singleton('request', function () {
            return new \Leaf\Http\Request();
        });

        $this->container->singleton('response', function () {
            return new \Leaf\Http\Response();
        });

        $this->container->singleton('headers', function () {
            return new \Leaf\Http\Headers();
        });

        Config::set('mode', _env('APP_ENV', $this->config('mode')));
        Config::set('app.instance', $this);
        Config::set('app.container', $this->container);
    }

    public function __get($name)
    {
        return $this->container->get($name);
    }

    public function __set($name, $value)
    {
        $this->container->set($name, $value);
    }

    public function __isset($name)
    {
        return $this->container->has($name);
    }

    public function __unset($name)
    {
        $this->container->remove($name);
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
        if ($value === null && is_string($name)) {
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
        call_user_func($code, $this, \Leaf\Config::get());
        $this->loadConfig();
        $this->setupErrorHandler();
    }

    /**
     * Swap out Leaf request instance
     *
     * @param mixed $class The new request class to attach
     * @deprecated
     */
    public function setRequestClass($class)
    {
        $this->container->singleton('request', function () use ($class) {
            return new $class();
        });
    }

    /**
     * Swap out Leaf response instance
     *
     * @param mixed $class The new response class to attach
     * @deprecated
     */
    public function setResponseClass($class)
    {
        $this->container->singleton('response', function () use ($class) {
            return new $class();
        });
    }

    /**
     * Evade CORS errors
     *
     * @param $options Config for cors
     */
    public function cors($options = [])
    {
        if (class_exists('Leaf\Http\Cors')) {
            Http\Cors::config($options);
        } else {
            trigger_error('Cors module not found! Run `leaf install cors` or `composer require leafs/cors` to install the CORS module. This is required to configure CORS.');
        }
    }

    /**
     * Create a route handled by websocket (requires Eien module)
     *
     * @param string $name The url of the route
     * @param callable $callback The callback function
     * @uses package Eien module
     * @see https://leafphp.dev/modules/eien/
     */
    public function ws(string $name, callable $callback)
    {
        Config::set('eien.events', array_merge(
            Config::get('eien.events') ?? [],
            [$name => $callback]
        ));
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
            trigger_error('You need to enable logging to use this feature! Set log.enabled to true and install the logger module');
        }

        return $this->log;
    }

    /********************************************************************************
     * Application Accessors
     *******************************************************************************/

    /**
     * Get the Request Headers
     * @return \Leaf\Http\Headers
     */
    public function headers()
    {
        return $this->headers;
    }

    /**
     * Get the Request object
     * @return \Leaf\Http\Request
     */
    public function request()
    {
        return $this->request;
    }

    /**
     * Get the Response object
     * @return \Leaf\Http\Response
     */
    public function response()
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
        return rtrim($_SERVER['DOCUMENT_ROOT'], '/') . rtrim($this->request->getScriptName(), '/') . '/';
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
        if (ob_get_level() !== 0) {
            ob_clean();
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
            $appMode = Config::get('mode') ?? 'development';

            if ($mode === $appMode) {
                return $callback();
            }
        });
    }

    /**
     * Run mode-specific code. Unlike script, this runs immedietly.
     *
     * @param string $mode The mode to run code in
     * @param callable $callback The code to run in selected mode.
     */
    public static function environment($mode, $callback)
    {
        $appMode = Config::get('mode') ?? 'development';

        if ($mode === $appMode) {
            return $callback();
        }
    }

    /**
     * @inheritdoc
     */
    public static function run(?callable $callback = null)
    {
        if (class_exists('Leaf\Eien\Server') && Config::get('eien.enabled')) {
            server()
                ->wrap(function () use ($callback) {
                    parent::run($callback);
                })
                ->listen();
        } else {
            return parent::run($callback);
        }
    }
}
