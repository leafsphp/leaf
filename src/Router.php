<?php

namespace Leaf;

/**
 * Leaf Router
 * ---------------
 * Default leaf routing engine.
 * Based on/adapted from `bramus/router`
 */
class Router
{
    /**
     * @var mixed Callable to be invoked if application error
     */
    protected static $error;

    /**
     * @var mixed Callable to be invoked if no matching routes are found
     */
    protected static $notFound;

    /**
     * @var array
     */
    protected static $hooks = [
        'leaf.before' => [[]],
        'leaf.before.router' => [[]],
        'leaf.before.dispatch' => [[]],
        'leaf.after.dispatch' => [[]],
        'leaf.after.router' => [[]],
        'leaf.after' => [[]]
    ];

    /**
     * @var array The route patterns and their handling functions
     */
    private static $afterRoutes = [];

    /**
     * @var array The route patterns and their handling functions
     */
    private static $appRoutes = [];

    /**
     * @var callable The function to be executed when no route has been matched
     */
    protected static $notFoundCallback;

    /**
     * @var callable The function to be executed when app is under maintainance
     */
    protected static $downCallback;

    /**
     * @var string Current base route, used for (sub)route mounting
     */
    private static $baseRoute = '';

    /**
     * @var string The Request Method that needs to be handled
     */
    private static $requestedMethod = '';

    /**
     * @var string The Server Base Path for Router Execution
     */
    private static $serverBasePath;

    /**
     * @var string Default Controllers Namespace
     */
    private static $namespace = '';

    /**
     * @var string App mode
     */
    protected static $mode = 'development';

    /**
     * @var bool Use debug mode?
     */
    protected static $debugMode = true;

    /**
     * @var \Leaf\App Instance of leaf
     */
    protected static $app;

    /**
     * @var array
     */
    protected static $middleware;

    public function __construct($mode = null, $debugMode = true, $app = null)
    {
        if ($mode) static::$mode = $mode;
        if ($app) static::$app = $app;
        static::$debugMode = $debugMode;
        static::$middleware = [$app];
    }

    public static function configure($mode = null, $debugMode = true, $app = null)
    {
        if ($mode) static::$mode = $mode;
        if ($app) static::$app = $app;
        static::$debugMode = $debugMode;
    }

    /**
     * Get all routes registered in app
     * 
     * @return array
     */
    public static function routes()
    {
        return static::$appRoutes;
    }

    /**
     * Store a route and a handling function to be executed when accessed using one of the specified methods.
     *
     * @param string          $methods Allowed methods, | delimited
     * @param string          $pattern A route pattern such as /about/system
     * @param object|callable $fn      The handling function to be executed
     */
    public static function match($methods, $pattern, $fn)
    {
        $pattern = static::$baseRoute . '/' . trim($pattern, '/');
        $pattern = static::$baseRoute ? rtrim($pattern, '/') : $pattern;
        foreach (explode('|', $methods) as $method) {
            static::$afterRoutes[$method][] = [
                'pattern' => $pattern,
                'fn' => $fn,
            ];
        }
        static::$appRoutes[] = [
            'methods' => explode('|', $methods),
            'pattern' => $pattern,
            'fn' => $fn,
        ];
    }

    /**
     * Shorthand for a route accessed using any method.
     *
     * @param string          $pattern A route pattern such as /about/system
     * @param object|callable $fn      The handling function to be executed
     */
    public static function all($pattern, $fn)
    {
        static::match('GET|POST|PUT|DELETE|OPTIONS|PATCH|HEAD', $pattern, $fn);
    }

    /**
     * Shorthand for a route accessed using GET.
     *
     * @param string          $pattern A route pattern such as /about/system
     * @param object|callable $fn      The handling function to be executed
     */
    public static function get($pattern, $fn)
    {
        static::match('GET', $pattern, $fn);
    }

    /**
     * Shorthand for a route accessed using POST.
     *
     * @param string          $pattern A route pattern such as /about/system
     * @param object|callable $fn      The handling function to be executed
     */
    public static function post($pattern, $fn)
    {
        static::match('POST', $pattern, $fn);
    }

    /**
     * Shorthand for a route accessed using PATCH.
     *
     * @param string          $pattern A route pattern such as /about/system
     * @param object|callable $fn      The handling function to be executed
     */
    public static function patch($pattern, $fn)
    {
        static::match('PATCH', $pattern, $fn);
    }

    /**
     * Shorthand for a route accessed using DELETE.
     *
     * @param string          $pattern A route pattern such as /about/system
     * @param object|callable $fn      The handling function to be executed
     */
    public static function delete($pattern, $fn)
    {
        static::match('DELETE', $pattern, $fn);
    }

    /**
     * Shorthand for a route accessed using PUT.
     *
     * @param string          $pattern A route pattern such as /about/system
     * @param object|callable $fn      The handling function to be executed
     */
    public static function put($pattern, $fn)
    {
        static::match('PUT', $pattern, $fn);
    }

    /**
     * Shorthand for a route accessed using OPTIONS.
     *
     * @param string          $pattern A route pattern such as /about/system
     * @param object|callable $fn      The handling function to be executed
     */
    public static function options($pattern, $fn)
    {
        static::match('OPTIONS', $pattern, $fn);
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
    public static function redirect($from, $to, $status = 302)
    {
        $handler = function () use ($to, $status) {
            return header('location: ' . $to, true, $status);
        };

        return static::get($from, $handler);
    }

    /**
     * Display a template for a route
     *
     * @param string $pattern A route pattern such as /about/system
     * @param string $fn      The handling function to be executed
     */
    public static function view($pattern, $template, $data = [])
    {
        static::$app->blade->configure("App/Views/", "storage/framework/views/");
        static::get($pattern, function() use($template, $data) {
            (new Http\Response)->markup(static::$app->blade->render($template, $data));
        });
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
    public static function resource(string $pattern, string $controller)
    {
        static::match("GET|HEAD", $pattern, "$controller@index");
        static::post("$pattern", "$controller@store");
        static::match("GET|HEAD", "$pattern/create", "$controller@create");
        static::match("POST|DELETE", "$pattern/{id}/delete", "$controller@destroy");
        static::match("POST|PUT|PATCH", "$pattern/{id}/edit", "$controller@update");
        static::match("GET|HEAD", "$pattern/{id}/edit", "$controller@edit");
        static::match("GET|HEAD", "$pattern/{id}", "$controller@show");
    }

    /**
     * Mounts a collection of callbacks onto a base route.
     *
     * @param string $baseRoute The route sub pattern to mount the callbacks on
     * @param callable $fn The callback method
     */
    public static function mount($baseRoute, $fn)
    {
        $curBaseRoute = static::$baseRoute;
        static::$baseRoute .= $baseRoute;
        
        call_user_func($fn);
        
        static::$baseRoute = $curBaseRoute;
    }

    /**
     * Alias for mount()
     * 
     * @param string $baseRoute The route sub pattern to mount the callbacks on
     * @param callable $fn The callback method
     */
    public static function group($baseRoute, $fn)
    {
        static::mount($baseRoute, $fn);
    }

    /**
     * Set a Default Lookup Namespace for Callable methods.
     *
     * @param string $namespace A given namespace
     */
    public static function setNamespace($namespace)
    {
        if (is_string($namespace)) {
            static::$namespace = $namespace;
        }
    }

    /**
     * Get the given Namespace before.
     *
     * @return string The given Namespace if exists
     */
    public static function getNamespace()
    {
        return static::$namespace;
    }

    // ------------------ main routing engine ----------------------

    private static function invoke($fn, $params = [])
    {
        if (is_callable($fn)) {
            call_user_func_array($fn,
                $params
            );
        }
        // If not, check the existence of special parameters
        elseif (stripos($fn, '@') !== false) {
            // Explode segments of given route
            list($controller, $method) = explode('@', $fn);
            // Adjust controller class if namespace has been set
            if (static::getNamespace() !== '') {
                $controller = static::getNamespace() . '\\' . $controller;
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
     * Handle a a set of routes: if a match is found, execute the relating handling function.
     *
     * @param array $routes       Collection of route patterns and their handling functions
     * @param bool  $quitAfterRun Does the handle function need to quit after one route was matched?
     *
     * @return int The number of routes handled
     */
    private static function handle($routes, $quitAfterRun = false)
    {
        // Counter to keep track of the number of routes we've handled
        $numHandled = 0;
        // The current page URL
        $uri = static::getCurrentUri();
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
                static::invoke($route['fn'], $params);
                ++$numHandled;
                // If we need to quit, then quit
                if ($quitAfterRun) {
                    break;
                }
            }
        }

        return $numHandled;
    }

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
    public static function run($callback = null)
    {
        set_error_handler(['\Leaf\Exception\General', 'handleErrors']);

        static::add(new \Leaf\Middleware\Flash());
        static::add(new \Leaf\Middleware\MethodOverride());

        // Invoke middleware and application stack
        static::$middleware[0]->call();

        // Send headers
        if (headers_sent() === false) {
            // Send status
            if (strpos(PHP_SAPI, 'cgi') === 0) {
                // header(sprintf('Status: %s', \Leaf\Http\Response::getMessageForCode($status)));
            } else {
                // header(sprintf('HTTP/%s %s', static::$config('http.version'), \Leaf\Http\Response::getMessageForCode($status)));
            }
        }

        // static::applyHook('leaf.before.router');

        static::$requestedMethod = static::getRequestMethod();

        // static::applyHook('leaf.before.dispatch');
        
        $numHandled = 0;

        if (isset(static::$afterRoutes[static::$requestedMethod])) {
            $numHandled = static::handle(static::$afterRoutes[static::$requestedMethod], true);
        }

        // static::applyHook('leaf.after.dispatch');
        
        if ($numHandled === 0) {
            if (static::$notFoundCallback) {
                static::invoke(static::$notFoundCallback);
            } else {
                header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            }
        } else {
            if ($callback && is_callable($callback)) {
                $callback();
            }
        }

        // if it originally was a HEAD request, clean up after ourselves by emptying the output buffer
        if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            ob_end_clean();
        }

        // static::applyHook('leaf.after.router');

        // static::applyHook('leaf.after');

        restore_error_handler();

        // return true if a route was handled, false otherwise
        return $numHandled !== 0;
    }

    // ------------------ server-ish stuff -------------------------

    /**
     * Get the request method used, taking overrides into account.
     *
     * @return string The Request method to handle
     */
    public static function getRequestMethod()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        // if it's a HEAD request override it to being GET and prevent any output, as per HTTP Specification
        // @url http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
        if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            ob_start();
            $method = 'GET';
        } else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // if it's a POST request, check for a method override header
            $headers = Http\Headers::all();
            if (isset($headers['X-HTTP-Method-Override']) && in_array($headers['X-HTTP-Method-Override'], ['PUT', 'DELETE', 'PATCH'])) {
                $method = $headers['X-HTTP-Method-Override'];
            }
        }
        return $method;
    }

    /**
     * Define the current relative URI.
     *
     * @return string
     */
    public static function getCurrentUri()
    {
        // Get the current Request URI and remove rewrite base path from it (= allows one to run the router in a sub folder)
        $uri = substr(rawurldecode($_SERVER['REQUEST_URI']), strlen(static::getBasePath()));
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
    public static function getBasePath()
    {
        // Check if server base path is defined, if not define it.
        if (static::$serverBasePath === null) {
            static::$serverBasePath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)) . '/';
        }
        return static::$serverBasePath;
    }

    /**
     * Explicilty sets the server base path. To be used when your entry script path differs from your entry URLs.
     * @see https://github.com/bramus/router/issues/82#issuecomment-466956078
     *
     * @param string
     */
    public static function setBasePath($serverBasePath)
    {
        static::$serverBasePath = $serverBasePath;
    }

    // -------------------- error handling --------------------

    /**
     * Set the 404 handling function.
     *
     * @param object|callable $fn The function to be executed
     */
    public static function set404($fn = null)
    {
        if (is_callable($fn)) {
            static::$notFoundCallback = $fn;
        } else {
            static::$notFoundCallback = function () {
                \Leaf\Exception\General::default404();
            };
        }
    }

    /**
     * Set a custom maintainace mode callback.
     *
     * @param callable $fn The function to be executed
     */
    public static function setDown(callable $fn)
    {
        if (is_callable($fn)) {
            static::$downCallback = $fn;
        } else {
            static::$downCallback = function () {
                \Leaf\Exception\General::defaultDown();
            };
        }
    }

    // ------------------ middleware ---------------------

    /**
     * Add middleware
     *
     * This method prepends new middleware to the application middleware stack.
     * The argument must be an instance that subclasses Leaf_Middleware.
     *
     * @param \Leaf\Middleware
     */
    public static function add(\Leaf\Middleware $newMiddleware)
    {
        if (in_array($newMiddleware, static::$middleware)) {
            $middleware_class = get_class($newMiddleware);
            throw new \RuntimeException("Circular Middleware setup detected. Tried to queue the same Middleware instance ({$middleware_class}) twice.");
        }

        $newMiddleware->setApplication(static::$app);
        $newMiddleware->setNextMiddleware(self::$middleware[0]);
        array_unshift(self::$middleware, $newMiddleware);
    }
}
