<?php
/**
 * Leaf PHP Framework - a PHP micro-framework
 *
 * @author        Michael Darko <mickdd22@gmail.com>
 * @copyright   2019-2020 Michael Darko
 * @link             http://www.leaf-docs.netlify.com
 * @license        MIT
 * @version       2.0.0
 * @package     Leaf
 */
namespace Leaf;

// Ensure mcrypt constants are defined even if mcrypt extension is not loaded
if (!defined('MCRYPT_MODE_CBC')) define('MCRYPT_MODE_CBC', 0);
if (!defined('MCRYPT_RIJNDAEL_256')) define('MCRYPT_RIJNDAEL_256', 0);

/**
 * Leaf Core package
 * @package  Leaf
 * @author   Michael Darko
 * @since    1.0.0
 *
 * @property \Leaf\Environment   $environment
 * @property \Leaf\Http\Response $response
 * @property \Leaf\Http\Request  $request
 */
class Leaf
{
    /**
     * @const string
     */
    const VERSION = '2.0.0';

    /**
     * @var \Leaf\Helpers\Set
     */
    public $container;

    /**
     * @var array[\Leaf]
     */
    protected static $apps = array();

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $middleware;

    /**
     * @var mixed Callable to be invoked if application error
     */
    protected $error;

    /**
     * @var mixed Callable to be invoked if no matching routes are found
     */
    protected $notFound;

    /**
     * @var array
     */
    protected $hooks = array(
        'leaf.before' => array(array()),
        'leaf.before.router' => array(array()),
        'leaf.before.dispatch' => array(array()),
        'leaf.after.dispatch' => array(array()),
        'leaf.after.router' => array(array()),
        'leaf.after' => array(array())
    );

    /********************************************************************************
    * PSR-0 Autoloader
    *
    * Do not use if you are using Composer to autoload dependencies.
    *******************************************************************************/

    /**
     * Leaf PSR-0 autoloader
     */
    public static function autoload($className)
    {
        $thisClass = str_replace(__NAMESPACE__.'\\', '', __CLASS__);

        $baseDir = __DIR__;

        if (substr($baseDir, -strlen($thisClass)) === $thisClass) {
            $baseDir = substr($baseDir, 0, -strlen($thisClass));
        }

        $className = ltrim($className, '\\');
        $fileName  = $baseDir;
        $namespace = '';
        if ($lastNsPos = strripos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  .= str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        if (file_exists($fileName)) {
            require $fileName;
        }
    }

    /**
     * Register Leaf's PSR-0 autoloader
     */
    public static function registerAutoloader()
    {
        spl_autoload_register(__NAMESPACE__ . "\\Leaf::autoload");
    }

    /********************************************************************************
    * Instantiation and Configuration
    *******************************************************************************/

    /**
     * Constructor
     * @param  array $userSettings Associative array of application settings
     */
    public function __construct(array $userSettings = array())
    {
        // Setup IoC container
        $this->container = new \Leaf\Helpers\Set();
        $this->container['settings'] = array_merge(static::getDefaultSettings(), $userSettings);

        // Default environment
        $this->container->singleton('environment', function ($c) {
            return \Leaf\Environment::getInstance();
        });

        // Default request
        $this->container->singleton('request', function ($c) {
            return new \Leaf\Http\Request($c['environment']);
        });

        // Default response
        $this->container->singleton('response', function ($c) {
            return new \Leaf\Http\Response();
        });

        // Default session
        $this->container->singleton('session', function ($c) {
            return new \Leaf\Http\Session();
        });

        //  Default DB
        $this->container->singleton('db', function ($c) {
            return new \Leaf\Db\Mysqli();
        });

        //  Default Date
        $this->container->singleton('date', function ($c) {
            return new \Leaf\Date();
        });

        //  Default FS
        $this->container->singleton('fs', function ($c) {
            return new \Leaf\FS();
        });

        //  Default Controller
        $this->container->singleton('controller', function ($c) {
            return new \Leaf\Controller();
        });

        //  Veins Templating
        $this->container->singleton('veins', function ($c) {
            return new \Leaf\Veins();
        });

        //  Veins Templating
        $this->container->singleton('blade', function ($c) {
            return new \Leaf\Blade();
        });


        // Default view
        $this->container->singleton('view', function ($c) {
            $viewClass = $c['settings']['view'];
            $templatesPath = $c['settings']['templates.path'];

            $view = ($viewClass instanceOf \Leaf\View) ? $viewClass : new $viewClass;
            $view->setTemplatesDirectory($templatesPath);
            return $view;
        });

        // Default log writer
        $this->container->singleton('logWriter', function ($c) {
            $logWriter = $c['settings']['log.writer'];

            return is_object($logWriter) ? $logWriter : new \Leaf\LogWriter($c['environment']['leaf.errors']);
        });

        // Default log
        $this->container->singleton('log', function ($c) {
            $log = new \Leaf\Log($c['logWriter']);
            $log->setEnabled($c['settings']['log.enabled']);
            $log->setLevel($c['settings']['log.level']);
            $env = $c['environment'];
            $env['leaf.log'] = $log;

            return $log;
        });

        // Default mode
        $this->container['mode'] = function ($c) {
            $mode = $c['settings']['mode'];

            if (isset($_ENV['LEAF_MODE'])) {
                $mode = $_ENV['LEAF_MODE'];
            } else {
                $envMode = getenv('LEAF_MODE');
                if ($envMode !== false) {
                    $mode = $envMode;
                }
            }

            return $mode;
        };

        // Define default middleware stack
        $this->middleware = array($this);
        $this->add(new \Leaf\Middleware\Flash());
        $this->add(new \Leaf\Middleware\MethodOverride());

        // Make default if first instance
        if (is_null(static::getInstance())) {
            $this->setName('default');
        }
    }

    /**
     * This method adds a method to the global leaf instance
     * Register a method and use it globally on the Leaf Object
     */
    public function register($name, $value) {
        return $this->container->singleton($name, $value);
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
     * Get application instance by name
     * @param  string    $name The name of the Leaf application
     * @return \Leaf\Leaf|null
     */
    public static function getInstance($name = 'default')
    {
        return isset(static::$apps[$name]) ? static::$apps[$name] : null;
    }

    /**
     * Set Leaf application name
     * @param  string $name The name of this Leaf application
     */
    public function setName($name)
    {
        $this->name = $name;
        static::$apps[$name] = $this;
    }

    /**
     * Get Leaf application name
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get default application settings
     * @return array
     */
    public static function getDefaultSettings()
    {
        return array(
            // Application
            'mode' => 'development',
            // Debugging
            'debug' => true,
            // Logging
            'log.writer' => null,
            'log.level' => \Leaf\Log::DEBUG,
            'log.enabled' => true,
            // View
            'templates.path' => './templates',
            'view' => '\Leaf\View',
            // Cookies
            'cookies.encrypt' => false,
            'cookies.lifetime' => '20 minutes',
            'cookies.path' => '/',
            'cookies.domain' => null,
            'cookies.secure' => false,
            'cookies.httponly' => false,
            // Encryption
            'cookies.secret_key' => 'CHANGE_ME',
            'cookies.cipher' => MCRYPT_RIJNDAEL_256,
            'cookies.cipher_mode' => MCRYPT_MODE_CBC,
            // HTTP
            'http.version' => '1.1',
            // Routing
            'routes.case_sensitive' => true
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
     * @param  mixed        $value If name is a string, the value of the setting identified by $name
     * @return mixed        The value of a setting if only one argument is a string
     */
    public function config($name, $value = null)
    {
        $c = $this->container;

        if (is_array($name)) {
            if (true === $value) {
                $c['settings'] = array_merge_recursive($c['settings'], $name);
            } else {
                $c['settings'] = array_merge($c['settings'], $name);
            }
        } elseif (func_num_args() === 1) {
            return isset($c['settings'][$name]) ? $c['settings'][$name] : null;
        } else {
            $settings = $c['settings'];
            $settings[$name] = $value;
            $c['settings'] = $settings;
        }
    }

    /********************************************************************************
    * Application Modes
    *******************************************************************************/

    /**
     * Get application mode
     *
     * This method determines the application mode. It first inspects the $_ENV
     * superglobal for key `LEAF_MODE`. If that is not found, it queries
     * the `getenv` function. Else, it uses the application `mode` setting.
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Configure Leaf for a given mode
     *
     * This method will immediately invoke the callable if
     * the specified mode matches the current application mode.
     * Otherwise, the callable is ignored. This should be called
     * only _after_ you initialize your Leaf app.
     *
     * @param  string $mode
     * @param  mixed  $callable
     * @return void
     */
    public function configureMode($mode, $callable)
    {
        if ($mode === $this->getMode() && is_callable($callable)) {
            call_user_func($callable);
        }
    }

    /********************************************************************************
    * Logging
    *******************************************************************************/

    /**
     * Get application log
     * @return \Leaf\Log
     */
    public function getLog()
    {
        return $this->log;
    }

    /********************************************************************************
    * Routing
    *******************************************************************************/

    /**
     * Add GET|POST|PUT|PATCH|DELETE route
     *
     * Adds a new route to the router with associated callable. This
     * route will only be invoked when the HTTP request's method matches
     * this route's method.
     *
     * ARGUMENTS:
     *
     * First:       string  The URL pattern (REQUIRED)
     * In-Between:  mixed   Anything that returns TRUE for `is_callable` (OPTIONAL)
     * Last:        mixed   Anything that returns TRUE for `is_callable` (REQUIRED)
     *
     * The first argument is required and must always be the
     * route pattern (ie. '/books/:id').
     *
     * The last argument is required and must always be the callable object
     * to be invoked when the route matches an HTTP request.
     *
     * You may also provide an unlimited number of in-between arguments;
     * each interior argument must be callable and will be invoked in the
     * order specified before the route's callable is invoked.
     *
     * USAGE:
     *
     * Leaf::get('/foo'[, middleware, middleware, ...], callable);
     *
     * @param   array (See notes above)
     * @return  \Leaf\Route
     */
    /**
     * @var array The route patterns and their handling functions
     */
    private $afterRoutes = [];
    /**
     * @var array The before middleware route patterns and their handling functions
     */
    private $beforeRoutes = [];
    /**
     * @var object|callable The function to be executed when no route has been matched
     */
    protected $notFoundCallback;
    /**
     * @var string Current base route, used for (sub)route mounting
     */
    private $baseRoute = '';
    /**
     * @var string The Request Method that needs to be handled
     */
    private $requestedMethod = '';
    /**
     * @var string The Server Base Path for Router Execution
     */
    private $serverBasePath;
    /**
     * @var string Default Controllers Namespace
     */
    private $namespace = '';
    private $route_is_matched = false;
    /**
     * Store a before middleware route and a handling function to be executed when accessed using one of the specified methods.
     *
     * @param string          $methods Allowed methods, | delimited
     * @param string          $pattern A route pattern such as /about/system
     * @param object|callable $fn      The handling function to be executed
     */
    public function before($methods, $pattern, $fn) {
        $pattern = $this->baseRoute . '/' . trim($pattern, '/');
        $pattern = $this->baseRoute ? rtrim($pattern, '/') : $pattern;
        foreach (explode('|', $methods) as $method) {
            $this->beforeRoutes[$method][] = [
                'pattern' => $pattern,
                'fn' => $fn,
            ];
        }
    }
    /**
     * Store a route and a handling function to be executed when accessed using one of the specified methods.
     *
     * @param string          $methods Allowed methods, | delimited
     * @param string          $pattern A route pattern such as /about/system
     * @param object|callable $fn      The handling function to be executed
     */
    public function match($methods, $pattern, $fn) {
        $pattern = $this->baseRoute . '/' . trim($pattern, '/');
        $pattern = $this->baseRoute ? rtrim($pattern, '/') : $pattern;
        foreach (explode('|', $methods) as $method) {
            $this->afterRoutes[$method][] = [
                'pattern' => $pattern,
                'fn' => $fn,
            ];
        }
    }
    /**
     * Add a route that sends an HTTP redirect
     *
     * @param string             $from
     * @param string|URI      $to
     * @param int                 $status
     *
     * @return redirect
     */
    public function redirect($from, $to, $status = 302) {
        $handler = function() use ($to, $status) {
            return header('location: '.$to, true, $code);
        };

        return $this->get($from, $handler);
    }
    /**
     * Shorthand for a route accessed using any method.
     *
     * @param string          $pattern A route pattern such as /about/system
     * @param object|callable $fn      The handling function to be executed
     */
    public function all($pattern, $fn) {
        $this->match('GET|POST|PUT|DELETE|OPTIONS|PATCH|HEAD', $pattern, $fn);
    }
    /**
     * Shorthand for a route accessed using GET.
     *
     * @param string          $pattern A route pattern such as /about/system
     * @param object|callable $fn      The handling function to be executed
     */
    public function get($pattern, $fn) {
        $this->match('GET', $pattern, $fn);
    }
    /**
     * Shorthand for a route accessed using POST.
     *
     * @param string          $pattern A route pattern such as /about/system
     * @param object|callable $fn      The handling function to be executed
     */
    public function post($pattern, $fn)  {
        $this->match('POST', $pattern, $fn);
    }
    /**
     * Shorthand for a route accessed using PATCH.
     *
     * @param string          $pattern A route pattern such as /about/system
     * @param object|callable $fn      The handling function to be executed
     */
    public function patch($pattern, $fn) {
        $this->match('PATCH', $pattern, $fn);
    }
    /**
     * Shorthand for a route accessed using DELETE.
     *
     * @param string          $pattern A route pattern such as /about/system
     * @param object|callable $fn      The handling function to be executed
     */
    public function delete($pattern, $fn) {
        $this->match('DELETE', $pattern, $fn);
    }
    /**
     * Shorthand for a route accessed using PUT.
     *
     * @param string          $pattern A route pattern such as /about/system
     * @param object|callable $fn      The handling function to be executed
     */
    public function put($pattern, $fn) {
        $this->match('PUT', $pattern, $fn);
    }
    /**
     * Shorthand for a route accessed using OPTIONS.
     *
     * @param string          $pattern A route pattern such as /about/system
     * @param object|callable $fn      The handling function to be executed
     */
    public function options($pattern, $fn) {
        $this->match('OPTIONS', $pattern, $fn);
    }
    /**
     * Mounts a collection of callbacks onto a base route.
     *
     * @param string   $baseRoute The route sub pattern to mount the callbacks on
     * @param callable $fn        The callback method
     */
    public function mount($baseRoute, $fn)  {
        // Track current base route
        $curBaseRoute = $this->baseRoute;
        // Build new base route string
        $this->baseRoute .= $baseRoute;
        // Call the callable
        call_user_func($fn);
        // Restore original base route
        $this->baseRoute = $curBaseRoute;
    }
    /**
     * Get all request headers.
     *
     * @return array The request headers
     */
    public function getRequestHeaders() {
        $headers = [];
        // If getallheaders() is available, use that
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            // getallheaders() can return false if something went wrong
            if ($headers !== false) {
                return $headers;
            }
        }
        // Method getallheaders() not available or went wrong: manually extract 'm
        foreach ($_SERVER as $name => $value) {
            if ((substr($name, 0, 5) == 'HTTP_') || ($name == 'CONTENT_TYPE') || ($name == 'CONTENT_LENGTH')) {
                $headers[str_replace([' ', 'Http'], ['-', 'HTTP'], ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
    /**
     * Get the request method used, taking overrides into account.
     *
     * @return string The Request method to handle
     */
    public function getRequestMethod() {
        // Take the method as found in $_SERVER
        $method = $_SERVER['REQUEST_METHOD'];
        // If it's a HEAD request override it to being GET and prevent any output, as per HTTP Specification
        // @url http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
        if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            ob_start();
            $method = 'GET';
        }
        // If it's a POST request, check for a method override header
        elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $headers = $this->getRequestHeaders();
            if (isset($headers['X-HTTP-Method-Override']) && in_array($headers['X-HTTP-Method-Override'], ['PUT', 'DELETE', 'PATCH'])) {
                $method = $headers['X-HTTP-Method-Override'];
            }
        }
        return $method;
    }
    /**
     * Set a Default Lookup Namespace for Callable methods.
     *
     * @param string $namespace A given namespace
     */
    public function setNamespace($namespace) {
        if (is_string($namespace)) {
            $this->namespace = $namespace;
        }
    }
    /**
     * Get the given Namespace before.
     *
     * @return string The given Namespace if exists
     */
    public function getNamespace()  {
        return $this->namespace;
    }
    
    /**
     * Set the 404 handling function.
     *
     * @param object|callable $fn The function to be executed
     */
    public function set404($fn = null) {
        if (is_callable($fn)) {
            $this->notFoundCallback = $fn;
        } else {            
            $this->notFoundCallback = function() {
                $this->default404();
            };
        }
    }
    /**
     * Handle a a set of routes: if a match is found, execute the relating handling function.
     *
     * @param array $routes       Collection of route patterns and their handling functions
     * @param bool  $quitAfterRun Does the handle function need to quit after one route was matched?
     *
     * @return int The number of routes handled
     */
    private function handle($routes, $quitAfterRun = false) {
        // Counter to keep track of the number of routes we've handled
        $numHandled = 0;
        // The current page URL
        $uri = $this->getCurrentUri();
        // Loop all routes
        foreach ($routes as $route) {
            // Replace all curly braces matches {} into word patterns (like Laravel)
            $route['pattern'] = preg_replace('/\/{(.*?)}/', '/(.*?)', $route['pattern']);
            // we have a match!
            if (preg_match_all('#^' . $route['pattern'] . '$#', $uri, $matches, PREG_OFFSET_CAPTURE)) {
                // Rework matches to only contain the matches, not the orig string
                $matches = array_slice($matches, 1);
                // Extract the matched URL parameters (and only the parameters)
                $params = array_map(function ($match, $index) use ($matches) {
                    // We have a following parameter: take the substring from the current param position until the next one's position (thank you PREG_OFFSET_CAPTURE)
                    if (isset($matches[$index + 1]) && isset($matches[$index + 1][0]) && is_array($matches[$index + 1][0])) {
                        return trim(substr($match[0][0], 0, $matches[$index + 1][0][1] - $match[0][1]), '/');
                    } // We have no following parameters: return the whole lot
                    return isset($match[0][0]) ? trim($match[0][0], '/') : null;
                }, $matches, array_keys($matches));
                // Call the handling function with the URL parameters if the desired input is callable
                $this->invoke($route['fn'], $params);
                ++$numHandled;
                // If we need to quit, then quit
                if ($quitAfterRun) {
                    break;
                }
            }
        }
        // Return the number of routes handled
        return $numHandled;
    }
    private function invoke($fn, $params = []) {
        if (is_callable($fn)) {
            call_user_func_array($fn, $params);
        }
        // If not, check the existence of special parameters
        elseif (stripos($fn, '@') !== false) {
            // Explode segments of given route
            list($controller, $method) = explode('@', $fn);
            // Adjust controller class if namespace has been set
            if ($this->getNamespace() !== '') {
                $controller = $this->getNamespace() . '\\' . $controller;
            }
            // Check if class exists, if not just ignore and check if the class exists on the default namespace
            if (class_exists($controller)) {
                // First check if is a static method, directly trying to invoke it.
                // If isn't a valid static method, we will try as a normal method invocation.
                if (call_user_func_array([new $controller(), $method], $params) === false) {
                    // Try to call the method as an non-static method. (the if does nothing, only avoids the notice)
                    if (forward_static_call_array([$controller, $method], $params) === false);
                }
            }
        }
    }
    /**
     * Define the current relative URI.
     *
     * @return string
     */
    public function getCurrentUri() {
        // Get the current Request URI and remove rewrite base path from it (= allows one to run the router in a sub folder)
        $uri = substr(rawurldecode($_SERVER['REQUEST_URI']), strlen($this->getBasePath()));
        // Don't take query params into account on the URL
        if (strstr($uri, '?')) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }
        // Remove trailing slash + enforce a slash at the start
        return '/' . trim($uri, '/');
    }
    /**
     * Return server base Path, and define it if isn't defined.
     *
     * @return string
     */
    public function getBasePath() {
        // Check if server base path is defined, if not define it.
        if ($this->serverBasePath === null) {
            $this->serverBasePath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)) . '/';
        }
        return $this->serverBasePath;
    }
    /**
     * Explicilty sets the server base path. To be used when your entry script path differs from your entry URLs.
     * @see https://github.com/bramus/router/issues/82#issuecomment-466956078
     *
     * @param string
     */
    public function setBasePath($serverBasePath) {
        $this->serverBasePath = $serverBasePath;
    }

    // /**
    //  * Error Handler
    //  *
    //  * This method defines or invokes the application-wide Error handler.
    //  * There are two contexts in which this method may be invoked:
    //  *
    //  * 1. When declaring the handler:
    //  *
    //  * If the $argument parameter is callable, this
    //  * method will register the callable to be invoked when an uncaught
    //  * Exception is detected, or when otherwise explicitly invoked.
    //  * The handler WILL NOT be invoked in this context.
    //  *
    //  * 2. When invoking the handler:
    //  *
    //  * If the $argument parameter is not callable, Leaf assumes you want
    //  * to invoke an already-registered handler. If the handler has been
    //  * registered and is callable, it is invoked and passed the caught Exception
    //  * as its one and only argument. The error handler's output is captured
    //  * into an output buffer and sent as the body of a 500 HTTP Response.
    //  *
    //  * @param  mixed $argument Callable|\Exception
    //  */
    // public function error($argument = null)
    // {
    //     if (is_callable($argument)) {
    //         //Register error handler
    //         $this->error = $argument;
    //     } else {
    //         //Invoke error handler
    //         $this->response->status(500);
    //         $this->response->body('');
    //         $this->response->write($this->callErrorHandler($argument));
    //         $this->stop();
    //     }
    // }

    // /**
    //  * Call error handler
    //  *
    //  * This will invoke the custom or default error handler
    //  * and RETURN its output.
    //  *
    //  * @param  \Exception|null $argument
    //  * @return string
    //  */
    // protected function callErrorHandler($argument = null)
    // {
    //     ob_start();
    //     if (is_callable($this->error)) {
    //         call_user_func_array($this->error, array($argument));
    //     } else {
    //         call_user_func_array(array($this, 'defaultError'), array($argument));
    //     }

    //     return ob_get_clean();
    // }

    /********************************************************************************
    * Application Accessors
    *******************************************************************************/

    /**
     * Get a reference to the Environment object
     * @return \Leaf\Environment
     */
    public function environment()
    {
        return $this->environment;
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
     * Get the Router object
     * @return \Leaf\Router
     */
    // public function router()
    // {
    //     return $this->router;
    // }

    /**
     * Get and/or set the View
     *
     * This method declares the View to be used by the Leaf application.
     * If the argument is a string, Leaf will instantiate a new object
     * of the same class. If the argument is an instance of View or a subclass
     * of View, Leaf will use the argument as the View.
     *
     * If a View already exists and this method is called to create a
     * new View, data already set in the existing View will be
     * transferred to the new View.
     *
     * @param  string|\Leaf\View $viewClass The name or instance of a \Leaf\View subclass
     * @return \Leaf\View
     */
    public function view($viewClass = null)
    {
        if (!is_null($viewClass)) {
            $existingData = is_null($this->view) ? array() : $this->view->getData();
            if ($viewClass instanceOf \Leaf\View) {
                $this->view = $viewClass;
            } else {
                $this->view = new $viewClass();
            }
            $this->view->appendData($existingData);
            $this->view->setTemplatesDirectory($this->config('templates.path'));
        }

        return $this->view;
    }

    /********************************************************************************
    * Rendering
    *******************************************************************************/

    /**
     * Render a template
     *
     * Call this method within a GET, POST, PUT, PATCH, DELETE, NOT FOUND, or ERROR
     * callable to render a template whose output is appended to the
     * current HTTP response body. How the template is rendered is
     * delegated to the current View.
     *
     * @param  string $template The name of the template passed into the view's render() method
     * @param  array  $data     Associative array of data made available to the view
     * @param  int    $status   The HTTP response status code to use (optional)
     */
    public function render($template, $data = array(), $status = null)
    {
        if (!is_null($status)) {
            $this->response->status($status);
        }
        $this->view->appendData($data);
        $this->view->display($template);
    }

    /********************************************************************************
    * HTTP Caching
    *******************************************************************************/

    /**
     * Set Last-Modified HTTP Response Header
     *
     * Set the HTTP 'Last-Modified' header and stop if a conditional
     * GET request's `If-Modified-Since` header matches the last modified time
     * of the resource. The `time` argument is a UNIX timestamp integer value.
     * When the current request includes an 'If-Modified-Since' header that
     * matches the specified last modified time, the application will stop
     * and send a '304 Not Modified' response to the client.
     *
     * @param  int                       $time The last modified UNIX timestamp
     * @throws \InvalidArgumentException If provided timestamp is not an integer
     */
    public function lastModified($time)
    {
        if (is_integer($time)) {
            $this->response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s T', $time));
            if ($time === strtotime($this->request->headers->get('IF_MODIFIED_SINCE'))) {
                $this->halt(304);
            }
        } else {
            throw new \InvalidArgumentException('Leaf::lastModified only accepts an integer UNIX timestamp value.');
        }
    }

    /**
     * Set ETag HTTP Response Header
     *
     * Set the etag header and stop if the conditional GET request matches.
     * The `value` argument is a unique identifier for the current resource.
     * The `type` argument indicates whether the etag should be used as a strong or
     * weak cache validator.
     *
     * When the current request includes an 'If-None-Match' header with
     * a matching etag, execution is immediately stopped. If the request
     * method is GET or HEAD, a '304 Not Modified' response is sent.
     *
     * @param  string                    $value The etag value
     * @param  string                    $type  The type of etag to create; either "strong" or "weak"
     * @throws \InvalidArgumentException If provided type is invalid
     */
    public function etag($value, $type = 'strong')
    {
        //Ensure type is correct
        if (!in_array($type, array('strong', 'weak'))) {
            throw new \InvalidArgumentException('Invalid Leaf::etag type. Expected "strong" or "weak".');
        }

        //Set etag value
        $value = '"' . $value . '"';
        if ($type === 'weak') {
            $value = 'W/'.$value;
        }
        $this->response['ETag'] = $value;

        //Check conditional GET
        if ($etagsHeader = $this->request->headers->get('IF_NONE_MATCH')) {
            $etags = preg_split('@\s*,\s*@', $etagsHeader);
            if (in_array($value, $etags) || in_array('*', $etags)) {
                $this->halt(304);
            }
        }
    }

    /**
     * Set Expires HTTP response header
     *
     * The `Expires` header tells the HTTP client the time at which
     * the current resource should be considered stale. At that time the HTTP
     * client will send a conditional GET request to the server; the server
     * may return a 200 OK if the resource has changed, else a 304 Not Modified
     * if the resource has not changed. The `Expires` header should be used in
     * conjunction with the `etag()` or `lastModified()` methods above.
     *
     * @param string|int    $time   If string, a time to be parsed by `strtotime()`;
     *                              If int, a UNIX timestamp;
     */
    public function expires($time)
    {
        if (is_string($time)) {
            $time = strtotime($time);
        }
        $this->response->headers->set('Expires', gmdate('D, d M Y H:i:s T', $time));
    }

    /********************************************************************************
    * HTTP Cookies
    *******************************************************************************/

    /**
     * Set HTTP cookie to be sent with the HTTP response
     *
     * @param string     $name      The cookie name
     * @param string     $value     The cookie value
     * @param int|string $time      The duration of the cookie;
     *                                  If integer, should be UNIX timestamp;
     *                                  If string, converted to UNIX timestamp with `strtotime`;
     * @param string     $path      The path on the server in which the cookie will be available on
     * @param string     $domain    The domain that the cookie is available to
     * @param bool       $secure    Indicates that the cookie should only be transmitted over a secure
     *                              HTTPS connection to/from the client
     * @param bool       $httponly  When TRUE the cookie will be made accessible only through the HTTP protocol
     */
    public function setCookie($name, $value, $time = null, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        $settings = array(
            'value' => $value,
            'expires' => is_null($time) ? $this->config('cookies.lifetime') : $time,
            'path' => is_null($path) ? $this->config('cookies.path') : $path,
            'domain' => is_null($domain) ? $this->config('cookies.domain') : $domain,
            'secure' => is_null($secure) ? $this->config('cookies.secure') : $secure,
            'httponly' => is_null($httponly) ? $this->config('cookies.httponly') : $httponly
        );
        $this->response->cookies->set($name, $settings);
    }

    /**
     * Get value of HTTP cookie from the current HTTP request
     *
     * Return the value of a cookie from the current HTTP request,
     * or return NULL if cookie does not exist. Cookies created during
     * the current request will not be available until the next request.
     *
     * @param  string      $name
     * @param  bool        $deleteIfInvalid
     * @return string|null
     */
    public function getCookie($name, $deleteIfInvalid = true)
    {
        // Get cookie value
        $value = $this->request->cookies->get($name);

        // Decode if encrypted
        if ($this->config('cookies.encrypt')) {
            $value = \Leaf\Http\Util::decodeSecureCookie(
                $value,
                $this->config('cookies.secret_key'),
                $this->config('cookies.cipher'),
                $this->config('cookies.cipher_mode')
            );
            if ($value === false && $deleteIfInvalid) {
                $this->deleteCookie($name);
            }
        }

        /*
         * transform $value to @return doc requirement.
         * \Leaf\Http\Util::decodeSecureCookie -  is able
         * to return false and we have to cast it to null.
         */
        return $value === false ? null : $value;
    }

    /**
     * DEPRECATION WARNING! Use `setCookie` with the `cookies.encrypt` app setting set to `true`.
     *
     * Set encrypted HTTP cookie
     *
     * @param string    $name       The cookie name
     * @param mixed     $value      The cookie value
     * @param mixed     $expires    The duration of the cookie;
     *                                  If integer, should be UNIX timestamp;
     *                                  If string, converted to UNIX timestamp with `strtotime`;
     * @param string    $path       The path on the server in which the cookie will be available on
     * @param string    $domain     The domain that the cookie is available to
     * @param bool      $secure     Indicates that the cookie should only be transmitted over a secure
     *                              HTTPS connection from the client
     * @param  bool     $httponly   When TRUE the cookie will be made accessible only through the HTTP protocol
     */
    public function setEncryptedCookie($name, $value, $expires = null, $path = null, $domain = null, $secure = false, $httponly = false)
    {
        $this->setCookie($name, $value, $expires, $path, $domain, $secure, $httponly);
    }

    /**
     * DEPRECATION WARNING! Use `getCookie` with the `cookies.encrypt` app setting set to `true`.
     *
     * Get value of encrypted HTTP cookie
     *
     * Return the value of an encrypted cookie from the current HTTP request,
     * or return NULL if cookie does not exist. Encrypted cookies created during
     * the current request will not be available until the next request.
     *
     * @param  string       $name
     * @param  bool         $deleteIfInvalid
     * @return string|bool
     */
    public function getEncryptedCookie($name, $deleteIfInvalid = true)
    {
        return $this->getCookie($name, $deleteIfInvalid);
    }

    /**
     * Delete HTTP cookie (encrypted or unencrypted)
     *
     * Remove a Cookie from the client. This method will overwrite an existing Cookie
     * with a new, empty, auto-expiring Cookie. This method's arguments must match
     * the original Cookie's respective arguments for the original Cookie to be
     * removed. If any of this method's arguments are omitted or set to NULL, the
     * default Cookie setting values (set during Leaf::init) will be used instead.
     *
     * @param string    $name       The cookie name
     * @param string    $path       The path on the server in which the cookie will be available on
     * @param string    $domain     The domain that the cookie is available to
     * @param bool      $secure     Indicates that the cookie should only be transmitted over a secure
     *                              HTTPS connection from the client
     * @param  bool     $httponly   When TRUE the cookie will be made accessible only through the HTTP protocol
     */
    public function deleteCookie($name, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        $settings = array(
            'domain' => is_null($domain) ? $this->config('cookies.domain') : $domain,
            'path' => is_null($path) ? $this->config('cookies.path') : $path,
            'secure' => is_null($secure) ? $this->config('cookies.secure') : $secure,
            'httponly' => is_null($httponly) ? $this->config('cookies.httponly') : $httponly
        );
        $this->response->cookies->remove($name, $settings);
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
     * Halt
     *
     * Stop the application and immediately send the response with a
     * specific status and body to the HTTP client. This may send any
     * type of response: info, success, redirect, client error, or server error.
     * If you need to render a template AND customize the response status,
     * use the application's `render()` method instead.
     *
     * @param  int      $status     The HTTP response status
     * @param  string   $message    The HTTP response body
     */
    public function halt($status, $message = '')
    {
        $this->cleanBuffer();
        $this->response->status($status);
        $this->response->body($message);
        // $this->stop();
        // exit();
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

    /**
     * Set the HTTP response Content-Type
     * @param  string   $type   The Content-Type for the Response (ie. text/html)
     */
    public function contentType($type)
    {
        $this->response->headers->set('Content-Type', $type);
    }

    /**
     * Set the HTTP response status code
     * @param  int      $code     The HTTP response status code
     */
    public function status($code)
    {
        $this->response->setStatus($code);
    }

    /**
     * RedirectTo
     *
     * Redirects to a specific named route
     *
     * @param string    $route      The route name
     * @param array     $params     Associative array of URL parameters and replacement values
     */
    public function redirectTo($route, $params = array(), $status = 302){
        $this->redirect($this->urlFor($route, $params), $status);
    }

    /********************************************************************************
    * Flash Messages
    *******************************************************************************/

    /**
     * Set flash message for subsequent request
     * @param  string   $key
     * @param  mixed    $value
     */
    public function flash($key, $value)
    {
        if (isset($this->environment['leaf.flash'])) {
            $this->environment['leaf.flash']->set($key, $value);
        }
    }

    /**
     * Set flash message for current request
     * @param  string   $key
     * @param  mixed    $value
     */
    public function flashNow($key, $value)
    {
        if (isset($this->environment['leaf.flash'])) {
            $this->environment['leaf.flash']->now($key, $value);
        }
    }

    /**
     * Keep flash messages from previous request for subsequent request
     */
    public function flashKeep()
    {
        if (isset($this->environment['leaf.flash'])) {
            $this->environment['leaf.flash']->keep();
        }
    }

    /**
     * Get all flash messages
     */
    public function flashData()
    {
        if (isset($this->environment['leaf.flash'])) {
            return $this->environment['leaf.flash']->getMessages();
        }
    }

    /********************************************************************************
    * Hooks
    *******************************************************************************/

    /**
     * Assign hook
     * @param  string   $name       The hook name
     * @param  mixed    $callable   A callable object
     * @param  int      $priority   The hook priority; 0 = high, 10 = low
     */
    public function hook($name, $callable, $priority = 10)
    {
        if (!isset($this->hooks[$name])) {
            $this->hooks[$name] = array(array());
        }
        if (is_callable($callable)) {
            $this->hooks[$name][(int) $priority][] = $callable;
        }
    }

    /**
     * Invoke hook
     * @param  string $name The hook name
     * @param  mixed  ...   (Optional) Argument(s) for hooked functions, can specify multiple arguments
     */
    public function applyHook($name)
    {
        if (!isset($this->hooks[$name])) {
            $this->hooks[$name] = array(array());
        }
        if (!empty($this->hooks[$name])) {
            // Sort by priority, low to high, if there's more than one priority
            if (count($this->hooks[$name]) > 1) {
                ksort($this->hooks[$name]);
            }

            $args = func_get_args();
            array_shift($args);

            foreach ($this->hooks[$name] as $priority) {
                if (!empty($priority)) {
                    foreach ($priority as $callable) {
                        call_user_func_array($callable, $args);
                    }
                }
            }
        }
    }

    /**
     * Get hook listeners
     *
     * Return an array of registered hooks. If `$name` is a valid
     * hook name, only the listeners attached to that hook are returned.
     * Else, all listeners are returned as an associative array whose
     * keys are hook names and whose values are arrays of listeners.
     *
     * @param  string     $name     A hook name (Optional)
     * @return array|null
     */
    public function getHooks($name = null)
    {
        if (!is_null($name)) {
            return isset($this->hooks[(string) $name]) ? $this->hooks[(string) $name] : null;
        } else {
            return $this->hooks;
        }
    }

    /**
     * Clear hook listeners
     *
     * Clear all listeners for all hooks. If `$name` is
     * a valid hook name, only the listeners attached
     * to that hook will be cleared.
     *
     * @param  string   $name   A hook name (Optional)
     */
    public function clearHooks($name = null)
    {
        if (!is_null($name) && isset($this->hooks[(string) $name])) {
            $this->hooks[(string) $name] = array(array());
        } else {
            foreach ($this->hooks as $key => $value) {
                $this->hooks[$key] = array(array());
            }
        }
    }

    /********************************************************************************
    * Middleware
    *******************************************************************************/

    /**
     * Add middleware
     *
     * This method prepends new middleware to the application middleware stack.
     * The argument must be an instance that subclasses Leaf_Middleware.
     *
     * @param \Leaf\Middleware
     */
    public function add(\Leaf\Middleware $newMiddleware)
    {
        if(in_array($newMiddleware, $this->middleware)) {
            $middleware_class = get_class($newMiddleware);
            throw new \RuntimeException("Circular Middleware setup detected. Tried to queue the same Middleware instance ({$middleware_class}) twice.");
        }
        $newMiddleware->setApplication($this);
        $newMiddleware->setNextMiddleware($this->middleware[0]);
        array_unshift($this->middleware, $newMiddleware);
    }

    /********************************************************************************
    * Runner
    *******************************************************************************/

    /**
     * Run
     *
     * This method invokes the middleware stack, including the core Leaf application;
     * the result is an array of HTTP status, header, and body. These three items
     * are returned to the HTTP client.
     */
    /**
     * Execute the router: Loop all defined before middleware's and routes, and execute the handling function if a match was found.
     *
     * @param object|callable $callback Function to be executed after a matching route was handled (= after router middleware)
     *
     * @return bool
     */
    public function run($callback = null)  {
        set_error_handler(array('\Leaf\Leaf', 'handleErrors'));

        //Apply final outer middleware layers
        if ($this->config('debug')) {
            //Apply pretty exceptions only in debug to avoid accidental information leakage in production
            $this->add(new \Leaf\Middleware\PrettyExceptions());
        }

        //Invoke middleware and application stack
        $this->middleware[0]->call();

        //Fetch status, header, and body
        list($status, $headers, $body) = $this->response->finalize();

        // Serialize cookies (with optional encryption)
        \Leaf\Http\Util::serializeCookies($headers, $this->response->cookies, $this->settings);

        //Send headers
        if (headers_sent() === false) {
            //Send status
            if (strpos(PHP_SAPI, 'cgi') === 0) {
                header(sprintf('Status: %s', \Leaf\Http\Response::getMessageForCode($status)));
            } else {
                header(sprintf('HTTP/%s %s', $this->config('http.version'), \Leaf\Http\Response::getMessageForCode($status)));
            }

            //Send headers
            foreach ($headers as $name => $value) {
                $hValues = explode("\n", $value);
                foreach ($hValues as $hVal) {
                    header("$name: $hVal", false);
                }
            }
        }

        //Send body, but only if it isn't a HEAD request
        if (!$this->request->isHead()) {
            echo $body;
        }

        $this->applyHook('leaf.before.router');
        // Define which method we need to handle
        $this->requestedMethod = $this->getRequestMethod();
        // Handle all before middlewares
        if (isset($this->beforeRoutes[$this->requestedMethod])) {
            $this->handle($this->beforeRoutes[$this->requestedMethod]);
        }
        $this->applyHook('leaf.before.dispatch');
        // Handle all routes
        $numHandled = 0;
        if (isset($this->afterRoutes[$this->requestedMethod])) {
            $numHandled = $this->handle($this->afterRoutes[$this->requestedMethod], true);
        }
        $this->applyHook('leaf.after.dispatch');
        // If no route was handled, trigger the 404 (if any)
        if ($numHandled === 0) {
            if ($this->notFoundCallback) {
                $this->invoke($this->notFoundCallback);
            } else {
                header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            }
        } else {
            if ($callback && is_callable($callback)) {
                $callback();
            }
        }
        // If it originally was a HEAD request, clean up after ourselves by emptying the output buffer
        if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            ob_end_clean();
        }

        $this->applyHook('leaf.after.router');

        $this->applyHook('leaf.after');

        restore_error_handler();

        // Return true if a route was handled, false otherwise
        return $numHandled !== 0;
    }

    /**
     * Call
     *
     * This method finds and iterates all route objects that match the current request URI.
     */
    public function call()
    {
        try {
            if (isset($this->environment['leaf.flash'])) {
                $this->view()->setData('flash', $this->environment['leaf.flash']);
            }
            $this->applyHook('leaf.before');
            ob_start();
            
            $this->stop();
        } catch (\Leaf\Exception\Stop $e) {
            $this->response()->write(ob_get_clean());
        } catch (\Exception $e) {
            if ($this->config('debug')) {
                ob_end_clean();
                throw $e;
            } else {
                try {
                    $this->response()->write(ob_get_clean());
                    $this->error($e);
                } catch (\Leaf\Exception\Stop $e) {
                    // Do nothing
                }
            }
        }
    }

    /********************************************************************************
    * Error Handling and Debugging
    *******************************************************************************/

    /**
     * Convert errors into ErrorException objects
     *
     * This method catches PHP errors and converts them into \ErrorException objects;
     * these \ErrorException objects are then thrown and caught by Leaf's
     * built-in or custom error handlers.
     *
     * @param  int            $errno   The numeric type of the Error
     * @param  string         $errstr  The error message
     * @param  string         $errfile The absolute path to the affected file
     * @param  int            $errline The line number of the error in the affected file
     * @return bool
     * @throws \ErrorException
     */
    public static function handleErrors($errno, $errstr = '', $errfile = '', $errline = '')
    {
        if (!($errno & error_reporting())) {
            return;
        }

        throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
    }

    /**
     * Generate diagnostic template markup
     *
     * This method accepts a title and body content to generate an HTML document layout.
     *
     * @param  string   $title  The title of the HTML template
     * @param  string   $body   The body content of the HTML template
     * @return string
     */
    protected static function generateTemplateMarkup($title, $body)
    {
        return sprintf("<html><head><title>%s</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{display:inline-block;width:65px;}</style></head><body><h1 style=\"color: #038f03;\">%s</h1>%s</body></html>", $title, $title, $body);
    }

    /**
     * Default Not Found handler
     */
    protected function defaultNotFound()
    {
        echo static::generateTemplateMarkup('404 Page Not Found', '<p>The page you are looking for could not be found. Check the address bar to ensure your URL is spelled correctly. If all else fails, you can visit our home page at the link below.</p><a style="color: #038f03;" href="' . $this->request->getRootUri() . '/">Go back home</a>');
    }

    public function default404() {
        $this->defaultNotFound();
    }

    /**
     * Default Error handler
     */
    protected function defaultError($e)
    {
        $this->getLog()->error($e);
        echo self::generateTemplateMarkup('Error', '<p>A website error has occurred. The website administrator has been notified of the issue. Sorry for the temporary inconvenience.</p>');
    }
}
