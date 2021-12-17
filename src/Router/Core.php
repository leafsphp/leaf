<?php

namespace Leaf\Router;

/**
 * Leaf Router [Core]
 * ---------------
 * Core module for leaf router
 * 
 * @author Michael Darko
 * @since 3.0
 * @version 1.0
 */
class Core
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
        'mode' => 'development',
        'debug' => true,
        'app.down' => false,
    ];

    /**
     * 'Middleware' to run at specific times
     */
    protected static $hooks = [
        'router.before' => false,
        'router.before.route' => false,
        'router.before.dispatch' => false,
        'router.after.dispatch' => false,
        'router.after.route' => false,
        'router.after' => false,
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
    protected static $groupRoute = '';

    /**
     * Default controller namespace
     */
    protected static $namespace = '';

    /**
     * The Request Method that needs to be handled
     */
    protected static $requestedMethod = '';

    /**
     * The Server Base Path for Router Execution
     */
    protected static $serverBasePath = '';

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
    public static function getNamespace(): string
    {
        return static::$namespace;
    }

    /**
     * Map handler and options
     */
    protected static function mapHandler($handler, $options): array
    {
        if (is_array($handler)) {
            $handlerData = $handler;

            if (isset($handler['handler'])) {
                $handler = $handler['handler'];
                unset($handlerData['handler']);
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
                if (isset($value)) {
                    $options[$key] = $value;
                }
            }
        }

        return [$handler, $options];
    }

    /**
     * Add a router hook
     * 
     * Available hooks
     * - router.before
     * - router.before.route
     * - router.before.dispatch
     * - router.after.dispatch
     * - router.after.route
     * - router.after
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
     * @param \Leaf\Middleware $newMiddleware The middleware to set
     */
    public static function use(\Leaf\Middleware $newMiddleware)
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

    /**
     * Return server base Path, and define it if isn't defined.
     *
     * @return string
     */
    public static function getBasePath(): string
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
    public static function getCurrentUri(): string
    {
        // Get the current Request URI and remove rewrite base path from it (= allows one to run the router in a sub folder)
        $uri = substr(rawurldecode($_SERVER['REQUEST_URI']), strlen(static::getBasePath()));

        if (strstr($uri, '?')) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        return '/' . trim($uri, '/');
    }

    /**
     * Dispatch your application routes
     */
    public static function run(?callable $callback = null)
    {
        $config = static::$config;

        $mode = getenv('APP_ENV');
        $debug = getenv('APP_DEBUG');
        $appDown = getenv('APP_DOWN');

        if (class_exists('Leaf\App')) {
            $config = array_merge($config, [
                'mode' => $mode ?? \Leaf\Config::get('mode'),
                'app.down' => $appDown ?? \Leaf\Config::get('app.down'),
                'debug' => ($debug ?? \Leaf\Config::get('debug')) ?? $mode !== 'production',
            ]);
        }

        if ($config['app.down'] == 'true') {
            if (!static::$downHandler) {
                if (class_exists('Leaf\App')) {
                    static::$downHandler = function () {
                        \Leaf\Exception\General::defaultDown();
                    };
                } else {
                    static::$downHandler = function () {
                        echo 'App is down for maintenance';
                    };
                }
            }

            return static::invoke(static::$downHandler);
        }

        $middleware = static::$middleware;

        if (is_callable($callback)) {
            static::hook('router.after', $callback);
        }

        static::callHook('router.before');

        if (count($middleware) > 0) {
            $middleware[0]->call();
        }

        static::callHook('router.before.route');

        static::$requestedMethod = \Leaf\Http\Request::getMethod();

        if (isset(static::$routeSpecificMiddleware[static::$requestedMethod])) {
            static::handle(static::$routeSpecificMiddleware[static::$requestedMethod]);
        }

        static::callHook('router.before.dispatch');

        $numHandled = 0;

        if (isset(static::$routes[static::$requestedMethod])) {
            $numHandled = static::handle(
                static::$routes[static::$requestedMethod],
                true
            );
        }

        static::callHook('router.after.dispatch');

        if ($numHandled === 0) {
            if (!static::$notFoundHandler) {
                if (class_exists('Leaf\App')) {
                    static::$notFoundHandler = function () {
                        \Leaf\Exception\General::default404();
                    };
                } else {
                    static::$notFoundHandler = function () {
                        echo 'Route not found';
                    };
                }
            }

            static::invoke(static::$notFoundHandler);
        }

        // if it originally was a HEAD request, clean up after ourselves by emptying the output buffer
        if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            ob_end_clean();
        }

        static::callHook('router.after.route');

        restore_error_handler();

        return static::callHook('router.after') ?? ($numHandled !== 0);
    }

    /**
     * Handle a set of routes: if a match is found, execute the relating handling function.
     *
     * @param array $routes Collection of route patterns and their handling functions
     * @param bool $quitAfterRun Does the handle function need to quit after one route was matched?
     *
     * @return int The number of routes handled
     */
    private static function handle(array $routes, bool $quitAfterRun = false): int
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
                // Try to call the method as a non-static method. (the if does nothing, only avoids the notice)
                if (forward_static_call_array([$controller, $method], $params) === false);
            }
        }
    }
}
