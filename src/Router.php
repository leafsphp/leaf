<?php

declare(strict_types=1);

namespace Leaf;

/**
 * Leaf Router
 * ---------------
 * Super simple and powerful routing with Leaf
 *
 * @author Michael Darko
 * @since 1.2.0
 * @version 5.0
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
     * 'Middleware' to run at specific times
     */
    protected static $hooks = [
        'router.before' => [],
        'router.before.route' => [],
        'router.after.route' => [],
        'router.after' => [],
    ];

    /**
     * All middleware that should be run
     */
    protected static $middleware = [];

    /**
     * Named middleware
     */
    protected static $namedMiddleware = [];

    /**
     * All added routes and their handlers
     */
    protected static $routes = [];

    /**
     * Fast route lookup table for dispatching current request routes.
     */
    protected static $routeIndex = [];

    /**
     * Registration order used to preserve first-match routing semantics.
     */
    protected static $routeOrder = 0;

    /**
     * Sorted list of routes and their handlers
     */
    protected static $appRoutes = [];

    /**
     * All named routes
     */
    protected static $namedRoutes = [];

    /**
     * Route based middleware
     */
    protected static $routeGroupMiddleware = [];

    /**
     * Lingo options
     */
    protected static $lingoOptions = [
        'lingo.routes' => [],
    ];

    /**
     * Sitemap options
     */
    protected static $sitemapOptions = [];

    /**
     * Current group base path
     */
    protected static $groupRoute = '';

    /**
     * Default controller namespace
     */
    protected static $namespace = '';

    /**
     * The Server Base Path for Router Execution
     */
    protected static $serverBasePath = '';

    /**
     * Cached URI for the current request
     */
    protected static $currentUri;

    /**
     * Cached HTTP method for the current request
     */
    protected static $currentMethod;

    /**
     * Get the current request method, resolving overrides only once per request
     */
    protected static function getCurrentMethod(): string
    {
        return static::$currentMethod ??= \Leaf\Http\Request::getMethod();
    }

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
     * Set a custom maintenance mode callback.
     *
     * @param callable|null $handler The function to be executed
     */
    public static function setDown(?callable $handler = null)
    {
        static::$downHandler = $handler;
    }

    /**
     * Mounts a collection of callbacks onto a base route.
     *
     * @param string $path The route sub pattern/path to mount the callbacks on
     * @param callable|array $handler The callback method
     */
    public static function mount(string $path, $handler)
    {
        list($handler, $groupOptions) = static::mapHandler($handler);

        $initialNamespace = static::$namespace;
        $initialGroupRoute = static::$groupRoute;
        $initialLingoOptions = static::$lingoOptions;
        $initialSitemapOptions = static::$sitemapOptions;
        $initialGroupMiddleware = static::$routeGroupMiddleware;

        if ($groupOptions['namespace']) {
            static::$namespace = $groupOptions['namespace'];
        }

        static::$groupRoute = static::$groupRoute . ($path === '/' ? '' : (strpos($path, '/') !== 0 ? "/$path" : $path));

        if ($groupOptions['middleware']) {
            static::$routeGroupMiddleware = $groupOptions['middleware'];
        }

        if (isset($groupOptions['lingo.routes'])) {
            static::$lingoOptions['lingo.routes'] = $groupOptions['lingo.routes'];
        }

        if (isset($groupOptions['sitemap'])) {
            static::$sitemapOptions = $groupOptions['sitemap'];
        }

        call_user_func($handler);

        static::$namespace = $initialNamespace;
        static::$groupRoute = $initialGroupRoute;
        static::$lingoOptions = $initialLingoOptions;
        static::$sitemapOptions = $initialSitemapOptions;
        static::$routeGroupMiddleware = $initialGroupMiddleware;
    }

    /**
     * Alias for mount
     *
     * @param string $path The route sub pattern/path to mount the callbacks on
     * @param callable|array $handler The callback method
     */
    public static function group(string $path, $handler)
    {
        static::mount($path, $handler);
    }

    // ------------------- main routing stuff -----------------------

    /**
     * Store a route and it's handler
     *
     * @param string $methods Allowed HTTP methods (separated by `|`)
     * @param string $pattern The route pattern/path to match
     * @param string|array|callable $handler The handler for route when matched
     */
    public static function match(string $allowedMethods, string $pattern, $handler)
    {
        $methods = explode('|', $allowedMethods);

        $pattern = static::$groupRoute . '/' . trim($pattern, '/');
        $pattern = static::$groupRoute ? rtrim($pattern, '/') : $pattern;
        $compiledPattern = static::compilePattern($pattern);

        list($handler, $routeOptions) = static::mapHandler($handler);

        if (is_string($handler)) {
            $namespace = static::$namespace;

            if ($routeOptions['namespace']) {
                static::$namespace = $routeOptions['namespace'];
            }

            $handler = str_replace('\\\\', '\\', static::$namespace . "\\$handler");

            static::$namespace = $namespace;
        }

        foreach ($methods as $method) {
            $sitemapOptions = [];

            if (isset($routeOptions['sitemap'])) {
                $sitemapOptions = $routeOptions['sitemap'];
            } elseif (static::$sitemapOptions === false || (is_array(static::$sitemapOptions) && !empty(static::$sitemapOptions))) {
                $sitemapOptions = static::$sitemapOptions;
            }

            static::$routes[$method][] = [
                'pattern' => $pattern,
                'regex' => $compiledPattern['regex'],
                'params' => $compiledPattern['params'],
                'handler' => $handler,
                'name' => $routeOptions['name'] ?? '',
                'sitemap' => $sitemapOptions,
                'lingo.routes' => $routeOptions['lingo.routes'] ?? static::$lingoOptions['lingo.routes'] ?? [],
                'order' => static::$routeOrder++,
            ];

            static::indexRoute($method, static::$routes[$method][array_key_last(static::$routes[$method])]);

            if ($routeOptions['middleware'] || !empty(static::$routeGroupMiddleware)) {
                $routeMiddleware = $routeOptions['middleware'] ?? static::$routeGroupMiddleware;

                if (is_array($routeMiddleware)) {
                    foreach ($routeMiddleware as $middleware) {
                        static::$middleware[$method][] = [
                            'pattern' => $pattern,
                            'regex' => $compiledPattern['regex'],
                            'params' => $compiledPattern['params'],
                            'handler' => $middleware,
                        ];
                    }
                } else {
                    static::$middleware[$method][] = [
                        'pattern' => $pattern,
                        'regex' => $compiledPattern['regex'],
                        'params' => $compiledPattern['params'],
                        'handler' => $routeMiddleware,
                    ];
                }
            }
        }

        static::$appRoutes[] = [
            'methods' => $methods,
            'pattern' => $pattern,
            'regex' => $compiledPattern['regex'],
            'params' => $compiledPattern['params'],
            'handler' => $handler,
            'name' => $routeOptions['name'] ?? '',
        ];

        if ($routeOptions['name']) {
            static::$namedRoutes[$routeOptions['name']] = $pattern;
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
        static::match(
            'GET|POST|PUT|DELETE|OPTIONS|PATCH|HEAD',
            $pattern,
            $handler
        );
    }

    /**
     * Add a route with GET method
     *
     * @param string $pattern The route pattern/path to match
     * @param string|array|callable The handler for route when matched
     */
    public static function get(string $pattern, $handler)
    {
        static::match('GET', $pattern, $handler);
    }

    /**
     * Add a route with POST method
     *
     * @param string $pattern The route pattern/path to match
     * @param string|array|callable The handler for route when matched
     */
    public static function post(string $pattern, $handler)
    {
        static::match('POST', $pattern, $handler);
    }

    /**
     * Add a route with PUT method
     *
     * @param string $pattern The route pattern/path to match
     * @param string|array|callable The handler for route when matched
     */
    public static function put(string $pattern, $handler)
    {
        static::match('PUT', $pattern, $handler);
    }

    /**
     * Add a route with PATCH method
     *
     * @param string $pattern The route pattern/path to match
     * @param string|array|callable The handler for route when matched
     */
    public static function patch(string $pattern, $handler)
    {
        static::match('PATCH', $pattern, $handler);
    }

    /**
     * Add a route with OPTIONS method
     *
     * @param string $pattern The route pattern/path to match
     * @param string|array|callable The handler for route when matched
     */
    public static function options(string $pattern, $handler)
    {
        static::match('OPTIONS', $pattern, $handler);
    }

    /**
     * Add a route with DELETE method
     *
     * @param string $pattern The route pattern/path to match
     * @param string|array|callable The handler for route when matched
     */
    public static function delete(string $pattern, $handler)
    {
        static::match('DELETE', $pattern, $handler);
    }

    /**
     * Add a route with HEAD method
     *
     * @param string $pattern The route pattern/path to match
     * @param string|array|callable The handler for route when matched
     */
    public static function head(string $pattern, $handler)
    {
        static::match('HEAD', $pattern, $handler);
    }

    /**
     * Add a route that sends an HTTP redirect
     *
     * @param string $from The url to redirect from
     * @param string $to The url to redirect to
     * @param int $status The http status code for redirect
     */
    public static function redirect(
        string $from,
        string $to,
        int $status = 302
    ) {
        static::get($from, function () use ($to, $status) {
            return header("Location: $to", true, $status);
        });
    }

    /**
     * Add a route that renders a view
     *
     * @param string $pattern The route pattern/path to match
     * @param string $view The view to render
     * @param array $data The data to pass to the view
     */
    public static function view(string $pattern, string $view, $data = [])
    {
        static::get($pattern, function () use ($view, $data) {
            return response()->view($view, $data);
        });
    }

    /**
     * Add a route that renders an inertia view
     *
     * @param string $pattern The route pattern/path to match
     * @param string $view The view to render
     * @param array $data The data to pass to the view
     */
    public static function inertia(string $pattern, string $view, $data = [])
    {
        static::get($pattern, function () use ($view, $data) {
            return inertia($view, $data);
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
     * @param array|string $controller to handle route eg: PostController
     */
    public static function resource(string $pattern, $controller)
    {
        if (is_array($controller)) {
            $controllerToCall = $controller[0];

            $controller[0] = function () use ($pattern, $controllerToCall) {
                static::resource('/', $controllerToCall);
            };

            return static::group($pattern, $controller);
        }

        static::match('GET|HEAD', $pattern, "$controller@index");
        static::post($pattern, "$controller@store");
        static::match('GET|HEAD', "$pattern/create", "$controller@create");
        static::match('DELETE', "$pattern/{id}", "$controller@destroy");
        static::match('PUT|PATCH', "$pattern/{id}", "$controller@update");
        static::match('GET|HEAD', "$pattern/{id}/edit", "$controller@edit");
        static::match('GET|HEAD', "$pattern/{id}", "$controller@show");

        // still keeping DELETE and PUT|PATCH so earlier versions of leaf apps don't break
        static::match('POST|DELETE', "$pattern/{id}/delete", "$controller@destroy");
        static::match('POST|PUT|PATCH', "$pattern/{id}/edit", "$controller@update");
    }

    /**
     * Create a resource route for using controllers without the create and edit actions.
     *
     * This creates a routes that implement CRUD functionality in a controller
     * `/posts` creates:
     * - `/posts` - GET | HEAD - Controller@index
     * - `/posts` - POST - Controller@store
     * - `/posts/{id}` - GET | HEAD - Controller@show
     * - `/posts/{id}/edit` - POST | PUT | PATCH - Controller@update
     * - `/posts/{id}/delete` - POST | DELETE - Controller@destroy
     *
     * @param string $pattern The base route to use eg: /post
     * @param array|string $controller to handle route eg: PostController
     */
    public static function apiResource(string $pattern, $controller)
    {
        if (is_array($controller)) {
            $controllerToCall = $controller[0];

            $controller[0] = function () use ($pattern, $controllerToCall) {
                static::apiResource('/', $controllerToCall);
            };

            return static::group($pattern, $controller);
        }

        static::match('GET|HEAD', $pattern, "$controller@index");
        static::post($pattern, "$controller@store");
        static::match('GET|HEAD', "$pattern/{id}", "$controller@show");
        static::match('DELETE', "$pattern/{id}", "$controller@destroy");
        static::match('PUT|PATCH', "$pattern/{id}", "$controller@update");

        // still keeping DELETE and PUT|PATCH so earlier versions of leaf apps don't break
        static::match('POST|DELETE', "$pattern/{id}/delete", "$controller@destroy");
        static::match('POST|PUT|PATCH', "$pattern/{id}/edit", "$controller@update");
    }

    /**
     * Redirect to another route
     *
     * @param string|array $route The route to redirect to
     * @param array|null $data Data to pass to the next route
     *
     * @deprecated Use `Leaf\Http\Response::redirect` instead
     */
    public static function push($route, ?array $data = null)
    {
        if (is_array($route)) {
            if (!isset(static::$namedRoutes[$route[0]])) {
                trigger_error('Route named ' . $route[0] . ' not found');
            }

            $route = static::$namedRoutes[$route[0]];
        }

        if ($data) {
            $args = '?';

            foreach ($data as $key => $value) {
                $args .= http_build_query([$key => $value]) . '&';
            }

            $data = rtrim($args, '&');
        }

        return header("Location: $route$data");
    }

    /**
     * Get route url by defined route name
     *
     * @param string $routeName
     * @param array|string|null $params
     *
     * @return string
     */
    public static function route(string $routeName, $params = null): string
    {
        if (!isset(static::$namedRoutes[$routeName])) {
            trigger_error('Route named ' . $routeName . ' not found');

            return '';
        }

        $routePath = static::$namedRoutes[$routeName];
        if ($params) {
            if (is_array($params)) {
                foreach ($params as $key => $value) {
                    if (!preg_match('/{(' . preg_quote($key, '/') . ')(\?|:[^}]*)?}/', $routePath)) {
                        trigger_error('Param "' . $key . '" not found in route "' . static::$namedRoutes[$routeName] . '"');
                    }

                    $routePath = preg_replace('/{' . preg_quote($key, '/') . '(\?|:[^}]*)?}/', rawurlencode((string) $value), $routePath);
                }
            }
            if (is_string($params)) {
                $routePath = preg_replace('/{(.*?)}/', rawurlencode($params), $routePath);
            }
        }

        return $routePath;
    }

    /**
     * Force call the Leaf URL handler
     *
     * @param string $method The method to call
     * @param string $url The uri to force
     */
    public static function handleUrl(string $method, string $url)
    {
        if (isset(static::$routes[$method])) {
            static::handle(
                static::$routes[$method],
                true,
                $url
            );
        }
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
    protected static function mapHandler(
        $handler,
        $options = [
            'name' => null,
            'middleware' => null,
            'namespace' => null,
        ]
    ): array {
        $parsedHandler = $handler;
        $parsedOptions = $options;

        if (is_array($handler)) {
            if (is_string($handler['middleware'] ?? null)) {
                $parsedOptions['middleware'] = (class_exists($handler['middleware'])) ? function () use ($handler) {
                    (new $handler['middleware']())->call();
                } : static::$namedMiddleware[$handler['middleware']] ?? null;
            }

            if (is_array($handler['middleware'] ?? null)) {
                $parsedOptions['middleware'] = [];

                foreach ($handler['middleware'] as $middleware) {
                    if (is_string($middleware)) {
                        $parsedOptions['middleware'][] = static::$namedMiddleware[$middleware] ?? null;
                    } else {
                        $parsedOptions['middleware'][] = $middleware;
                    }
                }
            }

            if (isset($handler['handler'])) {
                $parsedHandler = $handler['handler'];
                unset($handler['handler']);
            } else {
                foreach ($handler as $key => $value) {
                    if (
                        (is_numeric($key) && is_callable($value))
                        || is_numeric($key) && is_string($value) && strpos($value, '@')
                    ) {
                        $parsedHandler = $value;
                        unset($handler[$key]);
                    } else {
                        $parsedOptions[$key] ??= $value;
                    }
                }
            }
        }

        return [$parsedHandler, $parsedOptions];
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

        static::$hooks[$name][] = $handler;
    }

    /**
     * Call a router hook
     *
     * @param string $name The hook to call
     */
    private static function callHook(string $name)
    {
        if (\count(static::$hooks[$name]) === 0) {
            return;
        }

        foreach (static::$hooks[$name] as $hook) {
            if (is_callable($hook)) {
                $context = $hook(['routes' => static::$routes]);

                if ($context && isset($context['routes']) && $context['routes'] !== static::$routes) {
                    static::$routes = $context['routes'];
                    static::reindexRoutes();
                }
            }
        }
    }

    /**
     * Rebuild the route index after routes are replaced at runtime (eg: by a router hook)
     */
    private static function reindexRoutes(): void
    {
        static::$routeIndex = [];

        foreach (static::$routes as $method => $routes) {
            foreach ($routes as $route) {
                $compiledPattern = static::compilePattern($route['pattern']);
                $route['regex'] = $compiledPattern['regex'];
                $route['params'] = $compiledPattern['params'];

                static::indexRoute($method, $route);
            }
        }
    }

    /**
     * Add middleware
     *
     * This method prepends new middleware to the application middleware stack.
     * The argument must be an instance that subclasses Leaf_Middleware.
     *
     * @param callable|string $middleware The middleware to set
     */
    public static function use($middleware)
    {
        if (is_string($middleware)) {
            $middleware = class_exists($middleware) ? function () use ($middleware) {
                (new $middleware())->call();
            } : static::$namedMiddleware[$middleware] ?? null;
        }

        if (!$middleware) {
            trigger_error('Middleware not found');

            return;
        }

        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD'];
        $compiledPattern = static::compilePattern('/.*');

        for ($i = 0; $i < count($methods); $i++) {
            static::$middleware[$methods[$i]][] = [
                'pattern' => '/.*',
                'regex' => $compiledPattern['regex'],
                'params' => $compiledPattern['params'],
                'handler' => $middleware,
            ];
        }
    }

    /**
     * Register a middleware in your Leaf application by name
     *
     * @param string $name The name of the middleware
     * @param callable $middleware The middleware to register
     */
    public function registerMiddleware(string $name, callable $middleware)
    {
        static::$namedMiddleware[$name] = $middleware;
    }

    /**
     * Return server base Path, and define it if isn't defined.
     *
     * @return string
     */
    public static function getBasePath(): string
    {
        if (static::$serverBasePath === '') {
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
        static::$currentUri = null;
    }

    /**
     * Define the current relative URI.
     *
     * @return string
     */
    public static function getCurrentUri(): string
    {
        if (static::$currentUri !== null) {
            return static::$currentUri;
        }

        $basePath = static::getBasePath();
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $requestPath = parse_url($requestUri, PHP_URL_PATH) ?: '/';
        $requestPath = rawurldecode($requestPath);

        // Early exit If base path doesn't match
        if (strncmp($requestPath, $basePath, strlen($basePath)) !== 0) {
            if (!static::$notFoundHandler) {
                static::$notFoundHandler = function () {
                    \Leaf\Exception\General::default404();
                };
            }
            static::invoke(static::$notFoundHandler);

            return '/';
        }

        // Get the current Request URI and remove rewrite base path from it (= allows one to run the router in a sub folder)
        $uri = substr($requestPath, strlen($basePath)) ?: '/';

        return static::$currentUri = '/' . trim($uri, '/');
    }

    /**
     * Get route info of the current route
     *
     * @return array The route info array
     */
    public static function getRoute(): array
    {
        $route = [];
        $currentRoute = static::findRoute();

        if (isset($currentRoute[0])) {
            $route = array_merge($route, [
                'pattern' => $currentRoute[0]['route']['pattern'],
                'path' => static::getCurrentUri(),
                'method' => static::getCurrentMethod(),
                'name' => $currentRoute[0]['route']['name'] ?? null,
                'handler' => $currentRoute[0]['route']['handler'],
                'params' => $currentRoute[0]['params'] ?? [],
            ]);
        }

        return array_merge($route);
    }

    /**
     * Find the current route
     *
     * @return array
     */
    public static function findRoute(
        ?array $routes = null,
        ?string $uri = null,
        $returnFirst = true
    ): array {
        $handledRoutes = [];
        $uri = $uri ?? static::getCurrentUri();

        if ($routes === null) {
            $method = static::getCurrentMethod();

            // exact matches win outright, so we can skip regex matching entirely
            $staticRoute = static::$routeIndex[$method]['static'][$uri][0] ?? null;

            if ($staticRoute && $returnFirst) {
                return [[
                    'params' => [],
                    'handler' => $staticRoute['handler'],
                    'route' => $staticRoute,
                ]];
            }

            $routes = static::candidateRoutes($method, $uri);
        }

        foreach ($routes as $route) {
            if (($route['static'] ?? false) && $route['pattern'] === $uri) {
                $handledRoutes[] = [
                    'params' => [],
                    'handler' => $route['handler'],
                    'route' => $route,
                ];

                if ($returnFirst) {
                    break;
                }

                continue;
            }

            if (!isset($route['regex'])) {
                $compiledPattern = static::compilePattern($route['pattern']);
                $route['regex'] = $compiledPattern['regex'];
                $route['params'] = $compiledPattern['params'];
            }

            // we have a match!
            if (preg_match($route['regex'], $uri, $matches)) {
                $routeData = [
                    'params' => static::extractParams($route['params'] ?? [], $matches),
                    'handler' => $route['handler'],
                    'route' => $route,
                ];

                $handledRoutes[] = $routeData;

                if ($returnFirst) {
                    break;
                }
            }
        }

        return $handledRoutes;
    }

    /**
     * Dispatch your application routes
     */
    public static function run(?callable $callback = null)
    {
        static::$currentUri = null;
        static::$currentMethod = null;

        $requestedMethod = static::getCurrentMethod();
        $appDown = _env('APP_DOWN', \Leaf\Anchor::toBool(\Leaf\Config::getStatic('app.down')) ?? false);

        if ($appDown === true || $appDown === 'true') {
            if (!static::$downHandler) {
                static::$downHandler = function () {
                    \Leaf\Exception\General::defaultDown();
                };
            }

            return static::invoke(static::$downHandler);
        }

        if (is_callable($callback)) {
            static::hook('router.after', $callback);
        }

        static::callHook('router.before');

        if (isset(static::$middleware[$requestedMethod])) {
            static::handle(static::$middleware[$requestedMethod]);
        }

        static::callHook('router.before.route');

        $numHandled = 0;

        if (isset(static::$routes[$requestedMethod])) {
            $numHandled = static::handle(
                null,
                true
            );
        }

        if ($numHandled === 0) {
            if (!static::$notFoundHandler) {
                static::$notFoundHandler = function () {
                    \Leaf\Exception\General::default404();
                };
            }

            static::invoke(static::$notFoundHandler);
        }

        // if it originally was a HEAD request, clean up after ourselves by emptying the output buffer
        if (($_SERVER['REQUEST_METHOD'] ?? null) == 'HEAD' && ob_get_level() > 0) {
            ob_end_clean();
        }

        static::callHook('router.after.route');

        return static::callHook('router.after') ?? ($numHandled !== 0);
    }

    /**
     * Handle a set of routes: if a match is found, execute the relating handling function.
     *
     * @param array $routes Collection of route patterns and their handling functions
     * @param bool $quitAfterRun Does the handle function need to quit after one route was matched?
     * @param string|null $uri The URI to call (automatically set if nothing is passed).
     *
     * @return int The number of routes handled
     */
    private static function handle(?array $routes = null, bool $quitAfterRun = false, ?string $uri = null): int
    {
        $uri = $uri ?? static::getCurrentUri();
        $routeToHandle = static::findRoute($routes, $uri, $quitAfterRun);

        if ($quitAfterRun) {
            foreach ($routeToHandle as $currentRoute) {
                static::invoke($currentRoute['handler'], $currentRoute['params']);
            }

            return count($routeToHandle);
        }

        // hacky solution to handle middleware catching all middleware with pattern (.*?)
        $routesToRun = array_filter($routeToHandle, function ($route) use ($uri) {
            return $route['route']['pattern'] === $uri || $route['route']['pattern'] === '/.*' || implode('/', $route['params'] ?? []) === ltrim($uri, '/');
        });

        if (empty($routesToRun)) {
            $routesToRun = $routeToHandle;
        }

        foreach ($routesToRun as $currentRoute) {
            static::invoke($currentRoute['handler'], $currentRoute['params']);
        }

        return count($routesToRun);
    }

    private static function invoke($handler, $params = [])
    {
        if (!empty($params)) {
            $params = array_filter($params, function ($param) {
                return $param !== null;
            });
        }

        if (is_callable($handler)) {
            call_user_func_array(
                $handler,
                $params
            );
        } elseif (stripos($handler, '@') !== false) {
            list($controller, $method) = explode('@', $handler);

            if (!class_exists($controller)) {
                trigger_error("$controller not found. Cross-check the namespace if you're sure the file exists");
            }

            if (!method_exists($controller, $method)) {
                trigger_error("$method method not found in $controller");
            }

            // First check if is a static method, directly trying to invoke it.
            // If isn't a valid static method, we will try as a normal method invocation.
            $instance = new $controller();

            if (method_exists($instance, '__middleware')) {
                $middlewareResponse = $instance->__middleware($method, $params);

                if ($middlewareResponse === false) {
                    return;
                }
            }

            call_user_func_array([$instance, $method], $params);
        } elseif (strpos($handler, ':') !== false) {
            $middlewareParams = [];

            $middlewareParams = explode(':', $handler);
            $middleware = array_shift($middlewareParams);

            if (!isset(static::$namedMiddleware[$middleware])) {
                trigger_error("Middleware named $middleware not found");

                return;
            }

            static::$namedMiddleware[$middleware](explode('|', $middlewareParams[0] ?? ''));
        }
    }

    /**
     * Reset static router state.
     *
     * Useful for tests, workers, and long-running processes.
     */
    public static function reset(): void
    {
        static::$notFoundHandler = null;
        static::$downHandler = null;
        static::$hooks = [
            'router.before' => [],
            'router.before.route' => [],
            'router.after.route' => [],
            'router.after' => [],
        ];
        static::$middleware = [];
        static::$namedMiddleware = [];
        static::$routes = [];
        static::$routeIndex = [];
        static::$routeOrder = 0;
        static::$appRoutes = [];
        static::$namedRoutes = [];
        static::$routeGroupMiddleware = [];
        static::$lingoOptions = [
            'lingo.routes' => [],
        ];
        static::$sitemapOptions = [];
        static::$groupRoute = '';
        static::$namespace = '';
        static::$serverBasePath = '';
        static::$currentUri = null;
        static::$currentMethod = null;
    }

    private static function indexRoute(string $method, array $route): void
    {
        if (!isset(static::$routeIndex[$method])) {
            static::$routeIndex[$method] = [
                'static' => [],
                'dynamic' => [],
                'fallback' => [],
            ];
        }

        if (empty($route['params']) && $route['pattern'] !== '/.*') {
            $route['static'] = true;
            static::$routeIndex[$method]['static'][$route['pattern']][] = $route;

            return;
        }

        $bucket = static::routeBucket($route['pattern']);
        $route['static'] = false;

        if ($bucket === '*') {
            static::$routeIndex[$method]['fallback'][] = $route;

            return;
        }

        static::$routeIndex[$method]['dynamic'][$bucket][] = $route;
    }

    private static function candidateRoutes(string $method, string $uri): array
    {
        $index = static::$routeIndex[$method] ?? null;

        if (!$index) {
            return static::$routes[$method] ?? [];
        }

        // static routes outrank dynamic routes, which outrank catch-alls.
        // Registration order only breaks ties within a tier.
        $candidates = $index['static'][$uri] ?? [];
        $bucket = static::uriBucket($uri);

        if (isset($index['dynamic'][$bucket])) {
            $candidates = array_merge($candidates, $index['dynamic'][$bucket]);
        }

        if (!empty($index['fallback'])) {
            $candidates = array_merge($candidates, $index['fallback']);
        }

        return $candidates;
    }

    private static function routeBucket(string $pattern): string
    {
        $segment = explode('/', trim($pattern, '/'), 2)[0] ?? '';

        if ($segment === '' || strpos($segment, '{') !== false || strpos($segment, '(') !== false || strpos($segment, '*') !== false) {
            return '*';
        }

        return $segment;
    }

    private static function uriBucket(string $uri): string
    {
        return explode('/', trim($uri, '/'), 2)[0] ?? '';
    }

    protected static function compilePattern(string $pattern): array
    {
        if ($pattern === '/.*') {
            return ['regex' => '#^/.*$#', 'params' => []];
        }

        $params = [];
        $regex = '';
        $offset = 0;

        if (preg_match_all('/{([A-Za-z_][A-Za-z0-9_]*)(\?)?(?::((?:[^{}]|\{[^{}]*\})+))?}/', $pattern, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $index => $match) {
                [$token, $position] = $match;
                $literal = preg_quote(substr($pattern, $offset, $position - $offset), '#');

                $name = $matches[1][$index][0];
                $isOptional = ($matches[2][$index][0] ?? '') === '?';
                $constraint = $matches[3][$index][0] ?: '[^/]+';

                $params[] = $name;

                // an optional param swallows its leading slash so /posts/{id?} also matches /posts
                if ($isOptional && substr($literal, -1) === '/' && $regex . $literal !== '/') {
                    $regex .= substr($literal, 0, -1) . "(?:/(?P<$name>$constraint))?";
                } else {
                    $regex .= $literal;
                    $regex .= $isOptional ? "(?P<$name>$constraint)?" : "(?P<$name>$constraint)";
                }

                $offset = $position + strlen($token);
            }
        }

        if (!empty($params)) {
            $regex .= preg_quote(substr($pattern, $offset), '#');

            return ['regex' => '#^' . $regex . '$#', 'params' => $params];
        }

        return ['regex' => '#^' . preg_quote($pattern, '#') . '$#', 'params' => []];
    }

    protected static function extractParams(array $params, array $matches): array
    {
        if (!empty($params)) {
            $values = [];

            foreach ($params as $param) {
                $values[] = $matches[$param] ?? null;
            }

            return array_filter($values, function ($value) {
                return $value !== null && $value !== '';
            });
        }

        return array_values(array_slice(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY), 1));
    }
}
