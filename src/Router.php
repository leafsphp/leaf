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
     * Callable to be invoked on application error
     */
    protected $errorHandler;

    /**
     * Callable to be invoked if no matching routes are found
     */
    protected $notFoundHandler;

    /**
     * Callable to be invoked if app is down
     */
    protected $downHandler;

    /**
     * Router configuration
     */
    protected array $config = [
        "mode" => "development",
        "debug" => true,
    ];

    /**
     * "Middleware" to run at specific times
     */
    protected array $hooks = [
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
    protected array $middleware;

    /**
     * Route specific middleware
     */
    protected array $routeSpecificMiddleware;

    /**
     * All added routes and their handlers
     */
    protected array $routes = [];

    /**
     * Sorted list of routes and their handlers
     */
    protected array $appRoutes = [];

    /**
     * All named routes
     */
    protected array $namedRoutes = [];

    /**
     * Current route name
     */
    protected ?string $routeName = null;

    /**
     * Current group base path
     */
    protected string $groupRoute = "";

    /**
     * Current group prefix
     */
    protected string $groupPrefix = "";

    /**
     * Current group controller namespace
     */
    protected string $groupNamespace = "";

    /**
     * Default controller namespace
     */
    protected string $namespace = "";

    /**
     * The Request Method that needs to be handled
     */
    protected string $requestedMethod = '';

    /**
     * The Server Base Path for Router Execution
     */
    protected string $serverBasePath = "";

    /**
     * Instance of leaf
     */
    protected App $app;

    /**
     * Configure leaf router
     */
    public function configure(array $config)
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Add/Get Leaf App instance
     * 
     * @param App|null The leaf app instance to set
     */
    public function app(?App $app = null)
    {
        if (!$app) {
            return $this->app;
        }

        $this->app = $app;
    }

    /**
     * Get all routes registered in your leaf app
     */
    public function routes(): array
    {
        return $this->appRoutes;
    }

    /**
     * Name a route
     * 
     * @param string $name The name to give to route
     */
    public function name(string $name): self
    {
        $this->routeName = $name;
        return $this;
    }

    /**
     * Set a global namespace for your handlers
     * 
     * @param string $namespace The global namespace to set
     */
    public function setNamespace(string $namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * Get the global handler namespace.
     *
     * @return string The given namespace if exists
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Add a namespace to a route group
     * 
     * @param string $namespace The namespace to chain to group
     */
    public function namespace(string $namespace): self
    {
        $this->groupNamespace = $namespace;
        return $this;
    }
    
    /**
     * Add a prefix to a route group
     * 
     * @param string $prefix The prefix to add to group
     */
    public function prefix(string $prefix): self
    {
        $this->groupPrefix = $prefix;
        return $this;
    }

    /**
     * Mounts a collection of callbacks onto a base route.
     *
     * @param string $path The route sub pattern/path to mount the callbacks on
     * @param callable $handler The callback method
     */
    public function mount(string $path, callable $handler)
    {
        $namespace = $this->namespace;
        $groupRoute = $this->groupRoute;
        $groupPrefix = $this->groupPrefix;

        $this->namespace = $this->groupNamespace;
        $this->groupRoute = $groupPrefix . $path;

        call_user_func($handler);

        $this->groupNamespace = "";
        $this->groupPrefix = "";
        $this->namespace = $namespace;
        $this->groupRoute = $groupRoute;
    }

    /**
     * Alias for mount
     * 
     * @param string $path The route sub pattern/path to mount the callbacks on
     * @param callable $handler The callback method
     */
    public function group($path, $handler)
    {
        $this->mount($path, $handler);
    }

    // ------------------- main routing stuff -----------------------
    
    /**
     * Store a route and it's handler
     * 
     * @param string $methods Allowed HTTP methods (separated by `|`)
     * @param string $pattern The route pattern/path to match
     * @param string|object|callable The handler for route when matched
     */
    public function match(string $methods, string $pattern, $handler)
    {
        $pattern = $this->groupRoute . "/" . trim($pattern, "/");
        $pattern = $this->groupRoute ? rtrim($pattern, "/"): $pattern;

        if (is_string($handler)) {
            $handler = str_replace("\\\\", "\\", "{$this->namespace}\\$handler");
        }

        foreach (explode("|", $methods) as $method) {
            $this->routes[$method][] = [
                "pattern" => $pattern,
                "handler" => $handler,
                "name" => $this->routeName ?? ""
            ];
        }

        $this->appRoutes[] = [
            "methods" => explode("|", $methods),
            "pattern" => $pattern,
            "handler" => $handler,
            "name" => $this->routeName ?? ""
        ];

        if ($this->routeName) {
            $this->namedRoutes[$this->routeName] = $pattern;
        }

        $this->routeName = null;
    }

    /**
     * Add a route with all available HTTP methods
     * 
     * @param string $pattern The route pattern/path to match
     * @param string|object|callable The handler for route when matched
     */
    public function all(string $pattern, $handler)
    {
        return $this->match('GET|POST|PUT|DELETE|OPTIONS|PATCH|HEAD', $pattern, $handler);
    }

    /**
     * Add a route with GET method
     * 
     * @param string $pattern The route pattern/path to match
     * @param string|object|callable The handler for route when matched
     */
    public function get(string $pattern, $handler)
    {
        return $this->match('GET', $pattern, $handler);
    }

    /**
     * Add a route with POST method
     * 
     * @param string $pattern The route pattern/path to match
     * @param string|object|callable The handler for route when matched
     */
    public function post(string $pattern, $handler)
    {
        return $this->match('POST', $pattern, $handler);
    }

    /**
     * Add a route with PUT method
     * 
     * @param string $pattern The route pattern/path to match
     * @param string|object|callable The handler for route when matched
     */
    public function put(string $pattern, $handler)
    {
        return $this->match('PUT', $pattern, $handler);
    }

    /**
     * Add a route with PATCH method
     * 
     * @param string $pattern The route pattern/path to match
     * @param string|object|callable The handler for route when matched
     */
    public function patch(string $pattern, $handler)
    {
        return $this->match('PATCH', $pattern, $handler);
    }

    /**
     * Add a route with OPTIONS method
     * 
     * @param string $pattern The route pattern/path to match
     * @param string|object|callable The handler for route when matched
     */
    public function options(string $pattern, $handler)
    {
        return $this->match('OPTIONS', $pattern, $handler);
    }

    /**
     * Add a route with DELETE method
     * 
     * @param string $pattern The route pattern/path to match
     * @param string|object|callable The handler for route when matched
     */
    public function delete(string $pattern, $handler)
    {
        return $this->match('DELETE', $pattern, $handler);
    }

    /**
     * Add a route that sends an HTTP redirect
     *
     * @param string $from The url to redirect from
     * @param string $to The url to redirect to
     * @param int $status The http status code for redirect
     */
    public function redirect(string $from, string $to, int $status = 302)
    {
        $this->get($from, function () use ($to, $status) {
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
    public function resource(string $pattern, string $controller)
    {
        $this->match("GET|HEAD", $pattern, "$controller@index");
        $this->post("$pattern", "$controller@store");
        $this->match("GET|HEAD", "$pattern/create", "$controller@create");
        $this->match("POST|DELETE", "$pattern/{id}/delete", "$controller@destroy");
        $this->match("POST|PUT|PATCH", "$pattern/{id}/edit", "$controller@update");
        $this->match("GET|HEAD", "$pattern/{id}/edit", "$controller@edit");
        $this->match("GET|HEAD", "$pattern/{id}", "$controller@show");
    }

    // -------------------- error handling --------------------

    /**
     * Set the 404 handling function.
     *
     * @param object|callable $handler The function to be executed
     */
    public function set404($handler = null)
    {
        if (is_callable($handler)) {
            $this->notFoundHandler = $handler;
        } else {
            $this->notFoundHandler = function () {
                \Leaf\Exception\General::default404();
            };
        }
    }

    /**
     * Set a custom maintainace mode callback.
     *
     * @param callable $handler The function to be executed
     */
    public function setDown(?callable $handler = null)
    {
        $this->downHandler = $handler;
    }

    /**
     * Set a custom error screen.
     *
     * @param callable $handler The function to be executed
     */
    public function setError(callable $handler)
    {
        $this->errorHandler = $handler;
    }

    // ------------------- middleware and hooks ------------------

    /**
     * Add/Call a router hook
     * 
     * @param string $name The hook to set/call
     * @param callable|null $handler The hook handler
     */
    public function hook(string $name, ?callable $handler = null)
    {
        if (!isset($this->hooks[$name])) {
            trigger_error("$name is not a valid hook! Refer to the docs for all supported hooks");
        }

        if (!$handler) {
            return is_callable($this->hooks[$name]) ? $this->hooks[$name](): null;
        }

        $this->hooks[$name] = $handler;
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
        if (is_array($path)) {
            if (!isset($this->namedRoutes[$path[0]])) {
                trigger_error("Route named " . $path[0] . " not found");
            }

            $path = $this->namedRoutes[$path[0]];
        }

        $path = $this->groupRoute . '/' . trim($path, '/');
        $path = $this->groupRoute ? rtrim($path, '/') : $path;

        foreach (explode('|', $methods) as $method) {
            $this->routeSpecificMiddleware[$method][] = [
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
    public function add(\Leaf\Middleware $newMiddleware)
    {
        if (in_array($newMiddleware, $this->middleware)) {
            $middleware_class = get_class($newMiddleware);
            throw new \RuntimeException("Circular Middleware setup detected. Tried to queue the same Middleware instance ({$middleware_class}) twice.");
        }

        $newMiddleware->setApplication(Config::get("app")["instance"]);
        $newMiddleware->setNextMiddleware(self::$middleware[0]);

        array_unshift(self::$middleware, $newMiddleware);
    }

    // ----------------- misc functions and helpers ------------------

    /**
     * Redirect to another route
     * 
     * @param string|array $route The route to redirect to
     * @param array|null $data Data to pass to the next route
     */
    public function push($route, ?array $data = null)
    {
        if (is_array($route)) {
            if (!isset($this->namedRoutes[$route[0]])) {
                trigger_error("Route named " . $route[0] . " not found");
            }

            $route = $this->namedRoutes[$route[0]];
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
    public function run(?callable $callback = null)
    {
        if (Config::get("app.down")) {
            if (!$this->downHandler) {
                $this->downHandler = function () {
                    \Leaf\Exception\General::defaultDown();
                };
            }

            return $this->invoke($this->downHandler);
        }

        $middleware = [Config::get("app")["instance"]];

        if (is_callable($callback)) {
            $this->hook("router.after", $callback);
        }

        $this->hook("router.before");

        if (count($middleware) > 0) {
            $middleware[0]->call();
        }

        $this->hook("router.before.route");

        $this->requestedMethod = $this->getRequestMethod();

        if (isset($this->routeSpecificMiddleware[$this->requestedMethod])) {
            $this->handle($this->routeSpecificMiddleware[$this->requestedMethod]);
        }

        $this->hook("router.before.dispatch");

        $numHandled = 0;

        if (isset($this->routes[$this->requestedMethod])) {
            $numHandled = $this->handle(
                $this->routes[$this->requestedMethod],
                true
            );
        }

        $this->hook("router.after.dispatch");

        if ($numHandled === 0) {
            if (!$this->notFoundHandler) {
                $this->notFoundHandler = function () {
                    \Leaf\Exception\General::default404();
                };
            }

            $this->invoke($this->notFoundHandler);
        }

        // if it originally was a HEAD request, clean up after ourselves by emptying the output buffer
        if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            ob_end_clean();
        }

        $this->hook("router.after.route");

        restore_error_handler();

        return $this->hook("router.after") ?? ($numHandled !== 0);
    }

    // ------------------ server-ish stuff -------------------------

    /**
     * Get the request method used, taking overrides into account.
     *
     * @return string The Request method to handle
     */
    public function getRequestMethod()
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
    public function getBasePath()
    {
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
    public function setBasePath($serverBasePath)
    {
        $this->serverBasePath = $serverBasePath;
    }

    /**
     * Define the current relative URI.
     *
     * @return string
     */
    public function getCurrentUri()
    {
        // Get the current Request URI and remove rewrite base path from it (= allows one to run the router in a sub folder)
        $uri = substr(rawurldecode($_SERVER['REQUEST_URI']), strlen($this->getBasePath()));

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
    private function handle($routes, $quitAfterRun = false)
    {
        $numHandled = 0;
        $uri = $this->getCurrentUri();

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
                $this->invoke($route['handler'], $params);
                ++$numHandled;

                if ($quitAfterRun) {
                    break;
                }
            }
        }

        return $numHandled;
    }

    private function invoke($handler, $params = [])
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
