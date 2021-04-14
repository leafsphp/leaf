<?php

namespace Leaf;

/**
 * Leaf PHP Framework
 * --------
 * The easiest way to build simple but powerful apps and APIs quickly.
 *
 * @author Michael Darko <mickdd22@gmail.com>
 * @copyright 2019-2021 Michael Darko
 * @link https://leafphp.netlify.app/#/leaf/
 * @license MIT
 * @package Leaf
 */
class App
{
    /**
     * Leaf container instance 
     */
    public \Leaf\Helpers\Container $container;

    /**
     * The leaf router instance
     */
    protected \Leaf\Router $leafRouter;

    /********************************************************************************
     * Instantiation and Configuration
     *******************************************************************************/

    /**
     * Constructor
     * @param  array $userSettings Associative array of application settings
     */
    public function __construct(array $userSettings = [])
    {
        if (count($userSettings) > 0) {
            Config::set($userSettings);
        }

        // Setup IoC container
        $this->container = new \Leaf\Helpers\Container();
        $this->container['settings'] = Config::get();

        $this->leafRouter = new Router();

        $this->setupDefaultContainer();

        View::attach(\Leaf\BareUI::class, 'template');

        $this->loadViewEngines();

        set_error_handler(['\Leaf\Exception\General', 'handleErrors']);
    }

    /**
     * This method adds a method to the global leaf instance
     * Register a method and use it globally on the Leaf Object
     */
    public function register($name, $value)
    {
        return $this->container->singleton($name, $value);
    }

    public function loadViewEngines()
    {
        $views = View::$engines;

        if (count($views) > 0) {
            foreach ($views as $key => $value) {
                $this->container->singleton($key, function ($c) use ($value) {
                    return $value;
                });
            }
        }
    }

    private function setupDefaultContainer()
    {
        // Default request
        $this->container->singleton("request", function ($c) {
            return new \Leaf\Http\Request();
        });

        // Default response
        $this->container->singleton("response", function ($c) {
            return new \Leaf\Http\Response();
        });

        // Default headers
        $this->container->singleton("headers", function ($c) {
            return new \Leaf\Http\Headers();
        });

        // Default session
        $this->container->singleton("session", function ($c) {
            return new \Leaf\Http\Session();
        });

        //  Default DB
        $this->container->singleton("db", function ($c) {
            return new \Leaf\Db();
        });

        //  Default Date
        $this->container->singleton("date", function ($c) {
            return new \Leaf\Date();
        });

        //  Default FS
        $this->container->singleton("fs", function ($c) {
            return new \Leaf\FS();
        });

        if ($this->config("log.enabled")) {
            // Default log writer
            $this->container->singleton("logWriter", function ($c) {
                $logWriter = Config::get("log.writer");

                $file = $this->config("log.dir") . $this->config("log.file");

                return is_object($logWriter) ? $logWriter : new \Leaf\LogWriter($file, $this->config("log.open") ?? true);
            });

            // Default log
            $this->container->singleton("log", function ($c) {
                $log = new \Leaf\Log($c["logWriter"]);
                $log->enabled($this->config("log.enabled"));
                $log->level($this->config("log.level"));

                return $log;
            });
        }

        // Default mode
        $this->container["mode"] = function ($c) {
            $mode = $c["settings"]["mode"];

            if (isset($_ENV["LEAF_MODE"])) {
                $mode = $_ENV["LEAF_MODE"];
            } else {
                $envMode = getenv("LEAF_MODE");

                if ($envMode !== false) {
                    $mode = $envMode;
                }
            }

            return $mode;
        };

        Config::set("app", [
            "instance" => $this,
            "container" => $this->container,
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
        $c = $this->container;

        if ($value === null) {
            return Config::get($name);
        }

        $settings = [];

        if (is_array($name)) {
            if ($value === true) {
                $settings = array_merge_recursive($c['settings'], $name);
            } else {
                $settings = array_merge($c['settings'], $name);
            }
        } else {
            $settings = $c['settings'];
            $settings[$name] = $value;
        }

        $c['settings'] = $settings;
        Config::set($settings);
    }

    /********************************************************************************
     * Logging
     *******************************************************************************/

    /**
     * Get application log
     * 
     * @return \Leaf\Log
     */
    public function logger(): Log
    {
        if (!$this->log) {
            trigger_error("You need to set log.enabled to true to use this feature!");
        }

        return $this->log;
    }

    /********************************************************************************
     * Routing
     *******************************************************************************/

    /**
     * Store a route and a handling function to be executed when accessed using one of the specified methods.
     *
     * @param string $methods Allowed methods, | delimited
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $handler The handling function to be executed
     */
    public function match($methods, $pattern, $handler)
    {
        return $this->leafRouter->match($methods, $pattern, $handler);
    }

    /**
     * Shorthand for a route accessed using any method.
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $handler The handling function to be executed
     */
    public function all($pattern, $handler)
    {
        return $this->leafRouter->all($pattern, $handler);
    }

    /**
     * Shorthand for a route accessed using GET.
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $handler The handling function to be executed
     */
    public function get($pattern, $handler)
    {
        return $this->leafRouter->get($pattern, $handler);
    }

    /**
     * Shorthand for a route accessed using POST.
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $handler The handling function to be executed
     */
    public function post($pattern, $handler)
    {
        return $this->leafRouter->post($pattern, $handler);
    }

    /**
     * Shorthand for a route accessed using PATCH.
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $handler The handling function to be executed
     */
    public function patch($pattern, $handler)
    {
        return $this->leafRouter->patch($pattern, $handler);
    }

    /**
     * Shorthand for a route accessed using DELETE.
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $handler The handling function to be executed
     */
    public function delete($pattern, $handler)
    {
        return $this->leafRouter->delete($pattern, $handler);
    }

    /**
     * Shorthand for a route accessed using PUT.
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $handler The handling function to be executed
     */
    public function put($pattern, $handler)
    {
        return $this->leafRouter->put($pattern, $handler);
    }

    /**
     * Shorthand for a route accessed using OPTIONS.
     *
     * @param string $pattern A route pattern such as /about/system
     * @param object|callable $handler The handling function to be executed
     */
    public function options($pattern, $handler)
    {
        return $this->leafRouter->options($pattern, $handler);
    }

    /**
     * Add a route that sends an HTTP redirect
     *
     * @param string $from
     * @param string|URI $to
     * @param int $status
     *
     * @return redirect
     */
    public function redirect($from, $to, $status = 302)
    {
        return $this->leafRouter->redirect($from, $to, $status);
    }

    /**
     * Create a resource route for using controllers.
     * 
     * This creates a routes that implement CRUD functionality in a controller
     * `/posts` creates:
     * - `/posts` - GET | HEAD - Controller@index
     * - `/posts` - POST - Controller@store
     * - `/posts/{id}` - GET | HEAD - Controller@show
     * - `/posts/create` - GET | HEAD - Controller@create
     * - `/posts/{id}/edit` - GET | HEAD - Controller@edit
     * - `/posts/{id}/edit` - POST | PUT | PATCH - Controller@update
     * - `/posts/{id}/delete` - POST | DELETE - Controller@destroy
     * 
     * @param string $pattern The base route to use eg: /post
     * @param string $controller to handle route eg: PostController
     */
    public function resource(string $pattern, string $controller)
    {
        return $this->leafRouter->resource($pattern, $controller);
    }

    /**
     * Mounts a collection of callbacks onto a base route.
     *
     * @param string $baseRoute The route sub pattern to mount the callbacks on
     * @param callable $handler The callback method
     */
    public function mount($baseRoute, $handler)
    {
        $this->leafRouter->mount($baseRoute, $handler);
    }

    /**
     * Alias for mount()
     * 
     * @param string $baseRoute The route sub pattern to mount the callbacks on
     * @param callable $handler The callback method
     */
    public function group($baseRoute, $handler)
    {
        $this->leafRouter->group($baseRoute, $handler);
    }

    /**
     * Set a Default Lookup Namespace for Callable methods.
     *
     * @param string $namespace A given namespace
     */
    public function setNamespace($namespace)
    {
        $this->leafRouter->setNamespace($namespace);
    }

    /**
     * Add a namespace to a route group
     * 
     * @param string $namespace The namespace to chain to group
     */
    public function namespace(string $namespace): Router
    {
        return $this->leafRouter->namespace($namespace);
    }

    /**
     * Add a prefix to a route group
     * 
     * @param string $prefix The prefix to add to group
     */
    public function prefix(string $prefix): Router
    {
        return $this->leafRouter->prefix($prefix);
    }

    /**
     * Name a route
     * 
     * @param string $name The name to give to route
     */
    public function name(string $name): Router
    {
        return $this->leafRouter->name($name);
    }

    /**
     * Get the given Namespace before.
     *
     * @return string The given Namespace if exists
     */
    public function getNamespace(): string
    {
        return $this->leafRouter->getNamespace();
    }

    /**
     * Get all routes registered in app
     */
    public function routes(): array
    {
        return $this->leafRouter->routes();
    }

    /**
     * Get all routes registered in app
     */
    public function router(): Router
    {
        return $this->leafRouter;
    }

    /**
     * Error Handler
     *
     * This method defines or invokes the application-wide Error handler.
     * There are two contexts in which this method may be invoked:
     *
     * 1. When declaring the handler:
     *
     * If the $argument parameter is callable, this
     * method will register the callable to be invoked when an uncaught
     * Exception is detected, or when otherwise explicitly invoked.
     * The handler WILL NOT be invoked in this context.
     *
     * 2. When invoking the handler:
     *
     * If the $argument parameter is not callable, Leaf assumes you want
     * to invoke an already-registered handler. If the handler has been
     * registered and is callable, it is invoked and passed the caught Exception
     * as its one and only argument. The error handler's output is captured
     * into an output buffer and sent as the body of a 500 HTTP Response.
     *
     * @param mixed $argument Callable|\Exception
     */
    public function error($argument = null)
    {
        if (is_callable($argument)) {
            // Register error handler
            $this->error = $argument;
        } else {
            //Invoke error handler
            $this->response->status(500);
            $this->response()::markup($this->callErrorHandler($argument));

            $this->stop();
        }
    }

    /**
     * Call error handler
     *
     * This will invoke the custom or default error handler
     * and RETURN its output.
     *
     * @param  \Exception|null $argument
     * @return string
     */
    protected function callErrorHandler($argument = null)
    {
        ob_start();

        if (is_callable($this->error)) {
            call_user_func_array($this->error, [$argument]);
        } else {
            echo \Leaf\Exception\General::defaultError($argument, $this);
        }

        return ob_get_clean();
    }

    /**
     * Set the 404 handling function.
     *
     * @param object|callable $handler The function to be executed
     */
    public function set404($handler = null)
    {
        return $this->leafRouter->set404($handler);
    }

    /**
     * Set a custom maintainace mode callback.
     *
     * @param callable $handler The function to be executed
     */
    public function setDown($handler = null)
    {
        return $this->leafRouter->setDown($handler);
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
     * Get the Db object
     * @return \Leaf\Db
     */
    public function db()
    {
        return $this->db;
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
    public static function halt($status, $message = "")
    {
        if (ob_get_level() !== 0) {
            ob_clean();
        }

        Http\Headers::status($status);
        Http\Response::markup($message);

        exit();
    }

    /**
     * Stop
     *
     * The thrown exception will be caught in application's `call()` method
     * and the response will be sent as is to the HTTP client.
     *
     * @throws \Leaf\Exception\Stop
     */
    public function stop()
    {
        throw new \Leaf\Exception\Stop();
    }

    /**
     * Pass
     *
     * The thrown exception is caught in the application's `call()` method causing
     * the router's current iteration to stop and continue to the subsequent route if available.
     * If no subsequent matching routes are found, a 404 response will be sent to the client.
     *
     * @throws \Leaf\Exception\Pass
     */
    public function pass()
    {
        $this->cleanBuffer();
        throw new \Leaf\Exception\Pass();
    }

    /********************************************************************************
     * Middleware and hooks
     *******************************************************************************/

    /**
     * Add middleware
     *
     * This method prepends new middleware to the application middleware stack.
     * The argument must be an instance that subclasses Leaf_Middleware.
     *
     * @param \Leaf\Middleware
     */
    public function add(\Leaf\Middleware $middleware)
    {
        $this->leafRouter->add($middleware);
    }

    /**
     * Add a route specific middleware
     * 
     * @param string $methods Allowed methods, separated by |
     * @param string|array $path The path/route to apply middleware on
     * @param callable $handler The middleware handler
     */
    public function before(string $methods, $path, callable $handler)
    {
        $this->leafRouter->before($methods, $path, $handler);
    }

    /**
     * Add/Call a router hook
     * 
     * @param string $name The hook to set/call
     * @param callable|null $handler The hook handler
     */
    public function hook($name, $handler)
    {
        $this->leafRouter->hook($name, $handler);
    }

    /**
     * Evade CORS errors
     * 
     * Just a little bypass for common cors errors
     */
    public function evadeCors(bool $evadeOptions, string $allow_origin = "*", string $allow_headers = "*")
    {
        $this->response()->cors($allow_origin, $allow_headers);

        if ($evadeOptions) {
            if ($this->leafRouter->getRequestMethod() === "OPTIONS") {
                $this->response()->throwErr("ok", 200);
            }
        }
    }

    /********************************************************************************
     * Runner
     *******************************************************************************/
    /**
     * Execute the router: Loop all defined before middleware's and routes, and execute the handling function if a match was found.
     *
     * @param object|callable $callback Function to be executed after a matching route was handled (= after router middleware)
     *
     * @return bool
     */
    public function run($callback = null)
    {
        return $this->leafRouter->run($callback);
    }
}
