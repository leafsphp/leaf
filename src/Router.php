<?php

namespace Leaf;

/**
 * Leaf Router
 * ---------------
 * Super simple and powerful routing with Leaf
 * 
 * @author Michael Darko
 * @since 1.2.0
 * @version 2.0
 */
class Router
{
    /**
     * Callable to be invoked if no matching routes are found
     */
    protected static $notFoundHandler;

    /**
     * Callable to be invoked if app is down
     */
    protected static $downHandler;

    /**
     * Router configuration
     */
    protected static $config = [
        "mode" => "development",
        "debug" => true,
    ];

    /**
     * "Middleware" to run at specific times
     */
    protected static $hooks = [
        "router.before" => false,
        "router.before.route" => false,
        "router.before.dispatch" => false,
        "router.after.dispatch" => false,
        "router.after.route" => false,
        "router.after" => false,
    ];

    /**
     * Leaf app middleware
     */
    protected static $middleware = [];

    /**
     * Route specific middleware
     */
    protected static $routeSpecificMiddleware = [];

    /**
     * All added routes and their handlers
     */
    protected static $routes = [];

    /**
     * Sorted list of routes and their handlers
     */
    protected static $appRoutes = [];

    /**
     * All named routes
     */
    protected static $namedRoutes = [];

    /**
     * Current group base path
     */
    protected static $groupRoute = "";

    /**
     * Default controller namespace
     */
    protected static $namespace = "";

    /**
     * The Request Method that needs to be handled
     */
    protected static $requestedMethod = '';

    /**
     * The Server Base Path for Router Execution
     */
    protected static $serverBasePath = "";

    /**
     * Configure leaf router
     */
    public static function configure(array $config)
    {
        static::$config = array_merge(static::$config, $config);
    }

    /**
     * Get all routes registered in your leaf app
     */
    public static function routes(): array
    {
        return static::$appRoutes;
    }

    /**
     * Set a global namespace for your handlers
     * 
     * @param string $namespace The global namespace to set
     */
    public static function setNamespace(string $namespace)
    {
        static::$namespace = $namespace;
    }

    /**
     * Get the global handler namespace.
     *
     * @return string The given namespace if exists
     */
    public static function getNamespace()
    {
        return static::$namespace;
    }

    /**
     * Map handler and options
     */
    private static function mapHandler($handler, $options)
    {
        if (is_array($handler)) {
            $handlerData = $handler;

            if (isset($handler["handler"])) {
                $handler = $handler["handler"];
                unset($handlerData["handler"]);
            } else {
                foreach ($handler as $key => $value) {
                    if (
                        (is_numeric($key) && is_callable($value))
                        || is_numeric($key) && is_string($value) && strpos($value, "@")
                    ) {
                        $handler = $handler[$key];
                        unset($handlerData[$key]);
                        break;
                    }
                }
            }

            foreach ($handlerData as $key => $value) {
                if (isset($handlerData[$key])) {
                    $options[$key] = $handlerData[$key];
                }
            }
        }

        return [$handler, $options];
    }

    /**
     * Mounts a collection of callbacks onto a base route.
     *
     * @param string $path The route sub pattern/path to mount the callbacks on
     * @param callable|array $handler The callback method
     */
    public static function mount(string $path, $handler)
    {
        $groupOptions = [
            "namespace" => null,
        ];

        list($handler, $groupOptions) = static::mapHandler($handler, $groupOptions);

        $namespace = static::$namespace;
        $groupRoute = static::$groupRoute;

        if ($groupOptions["namespace"]) {
            static::$namespace = $groupOptions["namespace"];
        }

        static::$groupRoute = $path;

        call_user_func($handler);

        static::$namespace = $namespace;
        static::$groupRoute = $groupRoute;
    }

    /**
     * Alias for mount
     * 
     * @param string $path The route sub pattern/path to mount the callbacks on
     * @param callable|array $handler The callback method
     */
    public static function group($path, $handler)
    {
        static::mount($path, $handler);
    }

    // ------------------- main routing stuff -----------------------
    
    /**
     * Store a route and it's handler
     * 
     * @param string $methods Allowed HTTP methods (separated by `|`)
     * @param string $pattern The route pattern/path to match
     * @param string|array|callable The handler for route when matched
     */
    public static function match(string $methods, string $pattern, $handler)
    {
        $pattern = static::$groupRoute . "/" . trim($pattern, "/");
        $pattern = static::$groupRoute ? rtrim($pattern, "/"): $pattern;

        $routeOptions = [
            "name" => null,
            "middleware" => null,
            "namespace" => null,
        ];

        if (is_string($handler)) {
            $namespace = static::$namespace;

            if ($routeOptions["namespace"]) {
                static::$namespace = $routeOptions["namespace"];
            }

            $handler = str_replace("\\\\", "\\", static::$namespace . "\\$handler");

            static::$namespace = $namespace;
        }

        list($handler, $routeOptions) = static::mapHandler($handler, $routeOptions);

        foreach (explode("|", $methods) as $method) {
            static::$routes[$method][] = [
                "pattern" => $pattern,
                "handler" => $handler,
                "name" => $routeOptions["name"] ?? ""
            ];
        }

        static::$appRoutes[] = [
            "methods" => explode("|", $methods),
            "pattern" => $pattern,
            "handler" => $handler,
            "name" => $routeOptions["name"] ?? ""
        ];

        if ($routeOptions["name"]) {
            static::$namedRoutes[$routeOptions["name"]] = $pattern;
        }

        if ($routeOptions["middleware"]) {
            static::before($methods, $pattern, $routeOptions["middleware"]);
        }
    }

    /**
     * Add a route with all available HTTP methods
     * 
     * @param string $pattern The route pattern/path to match
     * @param string|array|callable The handler for route when matched
     */
    public static function all(string $pattern, $handler)
    {
        return static::match('GET|POST|PUT|DELETE|OPTIONS|PATCH|HEAD', $pattern, $handler);
    }

    /**
     * Add a route with GET method
     * 
     * @param string $pattern The route pattern/path to match
     * @param string|array|callable The handler for route when matched
     */
    public static function get(string $pattern, $handler)
    {
        return static::match('GET', $pattern, $handler);
    }

    /**
     * Add a route with POST method
     * 
     * @param string $pattern The route pattern/path to match
     * @param string|array|callable The handler for route when matched
     */
    public static function post(string $pattern, $handler)
    {
        return static::match('POST', $pattern, $handler);
    }

    /**
     * Add a route with PUT method
     * 
     * @param string $pattern The route pattern/path to match
     * @param string|array|callable The handler for route when matched
     */
    public static function put(string $pattern, $handler)
    {
        return static::match('PUT', $pattern, $handler);
    }

    /**
     * Add a route with PATCH method
     * 
     * @param string $pattern The route pattern/path to match
     * @param string|array|callable The handler for route when matched
     */
    public static function patch(string $pattern, $handler)
    {
        return static::match('PATCH', $pattern, $handler);
    }

    /**
     * Add a route with OPTIONS method
     * 
     * @param string $pattern The route pattern/path to match
     * @param string|array|callable The handler for route when matched
     */
    public static function options(string $pattern, $handler)
    {
        return static::match('OPTIONS', $pattern, $handler);
    }

    /**
     * Add a route with DELETE method
     * 
     * @param string $pattern The route pattern/path to match
     * @param string|array|callable The handler for route when matched
     */
    public static function delete(string $pattern, $handler)
    {
        return static::match('DELETE', $pattern, $handler);
    }

    /**
     * Add a route that sends an HTTP redirect
     *
     * @param string $from The url to redirect from
     * @param string $to The url to redirect to
     * @param int $status The http status code for redirect
     */
    public static function redirect(string $from, string $to, int $status = 302)
    {
        static::get($from, function () use ($to, $status) {
            return header("location: $to", true, $status);
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

    // -------------------- error handling --------------------

    /**
     * Set the 404 handling function.
     *
     * @param object|callable $handler The function to be executed
     */
    public static function set404($handler = null)
    {
        if (is_callable($handler)) {
            static::$notFoundHandler = $handler;
        } else {
            static::$notFoundHandler = function () {
                \Leaf\Exception\General::default404();
            };
        }
    }

    /**
     * Set a custom maintainace mode callback.
     *
     * @param callable $handler The function to be executed
     */
    public static function setDown(?callable $handler = null)
    {
        static::$downHandler = $handler;
    }

    // ------------------- middleware and hooks ------------------

    /**
     * Add a router hook
     * 
     * @param string $name The hook to set
     * @param callable|null $handler The hook handler
     */
    public static function hook(string $name, callable $handler)
    {
        if (!isset(static::$hooks[$name])) {
            trigger_error("$name is not a valid hook! Refer to the docs for all supported hooks");
        }

        static::$hooks[$name] = $handler;
    }

    /**
     * Call a router hook
     * 
     * @param string $name The hook to call
     */
    private static function callHook(string $name)
    {
        return is_callable(static::$hooks[$name]) ? static::$hooks[$name]() : null;
    }

    /**
     * Add a route specific middleware
     * 
     * @param string $methods Allowed methods, separated by |
     * @param string|array $path The path/route to apply middleware on
     * @param callable $handler The middleware handler
     */
    public static function before(string $methods, $path, callable $handler)
    {
        if (is_array($path)) {
            if (!isset(static::$namedRoutes[$path[0]])) {
                trigger_error("Route named " . $path[0] . " not found");
            }

            $path = static::$namedRoutes[$path[0]];
        }

        $path = static::$groupRoute . '/' . trim($path, '/');
        $path = static::$groupRoute ? rtrim($path, '/') : $path;

        foreach (explode('|', $methods) as $method) {
            static::$routeSpecificMiddleware[$method][] = [
                "pattern" => $path,
                "handler" => $handler,
            ];
        }
    }

    /**
     * Add middleware
     *
     * This method prepends new middleware to the application middleware stack.
     * The argument must be an instance that subclasses Leaf_Middleware.
     *
     * @param \Leaf\Middleware
     */
    public static function add($newMiddleware)
    {
        if (in_array($newMiddleware, static::$middleware)) {
            $middleware_class = get_class($newMiddleware);
            throw new \RuntimeException("Circular Middleware setup detected. Tried to queue the same Middleware instance ({$middleware_class}) twice.");
        }

        if (!empty(static::$middleware)) {
            $newMiddleware->setNextMiddleware(static::$middleware[0]);
        }

        array_unshift(static::$middleware, $newMiddleware);
    }

    // ----------------- misc functions and helpers ------------------

    /**
     * Redirect to another route
     * 
     * @param string|array $route The route to redirect to
     * @param array|null $data Data to pass to the next route
     */
    public static function push($route, ?array $data = null)
    {
        if (is_array($route)) {
            if (!isset(static::$namedRoutes[$route[0]])) {
                trigger_error("Route named " . $route[0] . " not found");
            }

            $route = static::$namedRoutes[$route[0]];
        }

        if ($data) {
            $args = "?";

            foreach ($data as $key => $value) {
                $args .= "$key=$value&";
            }

            $data = rtrim($args, "&");
        }

        return header("location: $route$data");
    }

    /**
     * Dispatch your application routes
     */
    public static function run(?callable $callback = null)
    {
        if (Config::get("app.down")) {
            if (!static::$downHandler) {
                static::$downHandler = function () {
                    \Leaf\Exception\General::defaultDown();
                };
            }

            return static::invoke(static::$downHandler);
        }

        $middleware = static::$middleware;

        if (is_callable($callback)) {
            static::hook("router.after", $callback);
        }

        static::callHook("router.before");

        if (count($middleware) > 0) {
            $middleware[0]->call();
        }

        static::callHook("router.before.route");

        static::$requestedMethod = static::getRequestMethod();

        if (isset(static::$routeSpecificMiddleware[static::$requestedMethod])) {
            static::handle(static::$routeSpecificMiddleware[static::$requestedMethod]);
        }

        static::callHook("router.before.dispatch");

        $numHandled = 0;

        if (isset(static::$routes[static::$requestedMethod])) {
            $numHandled = static::handle(
                static::$routes[static::$requestedMethod],
                true
            );
        }

        static::callHook("router.after.dispatch");

        if ($numHandled === 0) {
            if (!static::$notFoundHandler) {
                static::$notFoundHandler = function () {
                    \Leaf\Exception\General::default404();
                };
            }

            static::invoke(static::$notFoundHandler);
        }

        // if it originally was a HEAD request, clean up after ourselves by emptying the output buffer
        if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            ob_end_clean();
        }

        static::callHook("router.after.route");

        restore_error_handler();

        return static::callHook("router.after") ?? ($numHandled !== 0);
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
     * Return server base Path, and define it if isn't defined.
     *
     * @return string
     */
    public static function getBasePath()
    {
        if (static::$serverBasePath === "") {
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

    /**
     * Define the current relative URI.
     *
     * @return string
     */
    public static function getCurrentUri()
    {
        // Get the current Request URI and remove rewrite base path from it (= allows one to run the router in a sub folder)
        $uri = substr(rawurldecode($_SERVER['REQUEST_URI']), strlen(static::getBasePath()));

        if (strstr($uri, '?')) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        return '/' . trim($uri, '/');
    }

    // -------------------- main routing engine ------------------------

    /**
     * Handle a a set of routes: if a match is found, execute the relating handling function.
     *
     * @param array $routes Collection of route patterns and their handling functions
     * @param bool $quitAfterRun Does the handle function need to quit after one route was matched?
     *
     * @return int The number of routes handled
     */
    private static function handle($routes, $quitAfterRun = false)
    {
        $numHandled = 0;
        $uri = static::getCurrentUri();

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
                    }

                    // We have no following parameters: return the whole lot
                    return isset($match[0][0]) ? trim($match[0][0], '/') : null;
                }, $matches, array_keys($matches));

                // Call the handling function with the URL parameters if the desired input is callable
                static::invoke($route['handler'], $params);
                ++$numHandled;

                if ($quitAfterRun) {
                    break;
                }
            }
        }

        return $numHandled;
    }

    private static function invoke($handler, $params = [])
    {
        if (is_callable($handler)) {
            call_user_func_array(
                $handler,
                $params
            );
        }
        // If not, check the existence of special parameters
        elseif (stripos($handler, '@') !== false) {
            list($controller, $method) = explode('@', $handler);

            if (!class_exists($controller)) {
                trigger_error("$controller not found. Cross-check the namespace if you're sure the file exists");
            }

            if (!method_exists($controller, $method)) {
                trigger_error("$method method not found in $controller");
            }

            // First check if is a static method, directly trying to invoke it.
            // If isn't a valid static method, we will try as a normal method invocation.
            if (call_user_func_array([new $controller(), $method], $params) === false) {
                // Try to call the method as an non-static method. (the if does nothing, only avoids the notice)
                if (forward_static_call_array([$controller, $method], $params) === false);
            }
        }
    }
}
