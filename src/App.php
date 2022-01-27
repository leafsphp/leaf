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

        if (count($userSettings) > 0) {
            Config::set($userSettings);
        }

        if (class_exists('\Leaf\Anchor\CSRF')) {
            if (!Anchor\CSRF::token()) {
                Anchor\CSRF::init();
            }

            if (!Anchor\CSRF::verify()) {
                $csrfError = Anchor\CSRF::errors()['token'];
                Http\Response::status(400);
                echo Exception\General::csrf($csrfError);
                exit();
            }
        }

        $this->container = new \Leaf\Helpers\Container();

        $this->setupDefaultContainer();

        if (class_exists('\Leaf\BareUI')) {
            View::attach(\Leaf\BareUI::class, 'template');
        }

        $this->loadViewEngines();
    }

    protected function setupErrorHandler()
    {
        if ($this->config('debug') === true) {
            $debugConfig = [E_ALL, 1];
            $this->errorHandler = (new \Leaf\Exception\Run());
            $this->errorHandler->register();
        } else {
            $debugConfig = [0, 0];
            $this->setErrorHandler(['\Leaf\Exception\General', 'defaultError'], true);
        }

        error_reporting($debugConfig[0]);
        ini_set('display_errors', (string) $debugConfig[1]);
    }

    /**
     * Set a custom error screen.
     *
     * @param callable|array $handler The function to be executed
     */
    public function setErrorHandler($handler, bool $wrapper = true)
    {
        $errorHandler = $handler;

        if ($this->errorHandler instanceof \Leaf\Exception\Run) {
            $this->errorHandler->unregister();
        }

        if ($handler instanceof \Leaf\Exception\Handler\Handler) {
            $this->errorHandler = new \Leaf\Exception\Run();
            $this->errorHandler->pushHandler($handler)->register();
        }

        if ($wrapper) {
            $errorHandler = function ($errno, $errstr = '', $errfile = '', $errline = '') use ($handler) {
                $exception = Exception\General::toException($errno, $errstr, $errfile, $errline);
                Http\Response::status(500);
                call_user_func_array($handler, [$exception]);
                exit();
            };
        }

        set_error_handler($errorHandler);
    }

    /**
     * This method adds a method to the global leaf instance
     * Register a method and use it globally on the Leaf Object
     */
    public function register($name, $value)
    {
        $this->container->singleton($name, $value);
    }

    public function loadViewEngines()
    {
        $views = View::$engines;

        if (count($views) > 0) {
            foreach ($views as $key => $value) {
                $this->container->singleton($key, function () use ($value) {
                    return $value;
                });
            }
        }
    }

    private function setupDefaultContainer()
    {
        // Default request
        $this->container->singleton('request', function () {
            return new \Leaf\Http\Request();
        });

        // Default response
        $this->container->singleton('response', function () {
            return new \Leaf\Http\Response();
        });

        // Default headers
        $this->container->singleton('headers', function () {
            return new \Leaf\Http\Headers();
        });

        if ($this->config('log.enabled')) {
            if (class_exists('Leaf\Log')) {
                // Default log writer
                $this->container->singleton('logWriter', function ($c) {
                    $logWriter = Config::get('log.writer');

                    $file = $this->config('log.dir') . $this->config('log.file');

                    return is_object($logWriter) ? $logWriter : new \Leaf\LogWriter($file, $this->config('log.open') ?? true);
                });

                // Default log
                $this->container->singleton('log', function ($c) {
                    $log = new \Leaf\Log($c['logWriter']);
                    $log->enabled($this->config('log.enabled'));
                    $log->level($this->config('log.level'));

                    return $log;
                });
            }
        }

        // Default mode
        (function () {
            $mode = $this->config('mode');

            if (_env('APP_ENV')) {
                $mode = _env('APP_ENV');
            }

            if (_env('LEAF_MODE')) {
                $mode = _env('LEAF_MODE');
            }

            if (isset($_ENV['LEAF_MODE'])) {
                $mode = $_ENV['LEAF_MODE'];
            } else {
                $envMode = getenv('LEAF_MODE');

                if ($envMode !== false) {
                    $mode = $envMode;
                }
            }

            $this->config('mode', $mode);
        })();

        Config::set('app', [
            'instance' => $this,
            'container' => $this->container,
        ]);
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
        $this->setupErrorHandler();
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
        return rtrim($_SERVER['DOCUMENT_ROOT'], '/') . rtrim($this->request->getRootUri(), '/') . '/';
    }

    /**
     * Clean current output buffer
     */
    protected function cleanBuffer()
    {
        if (ob_get_level() !== 0) {
            ob_clean();
        }
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

        Http\Headers::status($status);
        Http\Response::markup($message);

        exit();
    }

    /**
     * Evade CORS errors
     *
     * Cors handler
     *
     * @param $options Config for cors
     */
    public function cors($options = [])
    {
        if (class_exists('Leaf\Http\Cors')) {
            Http\Cors::config($options);
        } else {
            trigger_error('Cors module not found! Run `composer require leafs/cors` to install the CORS module. This is required to configure CORS.');
        }
    }
}
