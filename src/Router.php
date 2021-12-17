<?php

namespace Leaf;

use Leaf\Router\Core;

/**
 * Leaf Router
 * ---------------
 * Super simple and powerful routing with Leaf
 * 
 * @author Michael Darko
 * @since 1.2.0
 * @version 3.0
 */
class Router extends Core
{
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
        $groupOptions = [
            'namespace' => null,
        ];

        list($handler, $groupOptions) = static::mapHandler(
            $handler,
            $groupOptions
        );

        $namespace = static::$namespace;
        $groupRoute = static::$groupRoute;

        if ($groupOptions['namespace']) {
            static::$namespace = $groupOptions['namespace'];
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
    public static function match(string $methods, string $pattern, $handler)
    {
        $pattern = static::$groupRoute . '/' . trim($pattern, '/');
        $pattern = static::$groupRoute ? rtrim($pattern, '/') : $pattern;

        $routeOptions = [
            'name' => null,
            'middleware' => null,
            'namespace' => null,
        ];

        if (is_string($handler)) {
            $namespace = static::$namespace;

            if ($routeOptions['namespace']) {
                static::$namespace = $routeOptions['namespace'];
            }

            $handler = str_replace('\\\\', '\\', static::$namespace . '\\$handler');

            static::$namespace = $namespace;
        }

        list($handler, $routeOptions) = static::mapHandler(
            $handler,
            $routeOptions
        );

        foreach (explode('|', $methods) as $method) {
            static::$routes[$method][] = [
                'pattern' => $pattern,
                'handler' => $handler,
                'name' => $routeOptions['name'] ?? ''
            ];
        }

        static::$appRoutes[] = [
            'methods' => explode('|', $methods),
            'pattern' => $pattern,
            'handler' => $handler,
            'name' => $routeOptions['name'] ?? ''
        ];

        if ($routeOptions['name']) {
            static::$namedRoutes[$routeOptions['name']] = $pattern;
        }

        if ($routeOptions['middleware']) {
            static::before($methods, $pattern, $routeOptions['middleware']);
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
            header("location: $to", true, $status);
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
        static::match('GET|HEAD', $pattern, "$controller@index");
        static::post($pattern, "$controller@store");
        static::match('GET|HEAD', "$pattern/create", "$controller@create");
        static::match('POST|DELETE', "$pattern/{id}/delete", "$controller@destroy");
        static::match('POST|PUT|PATCH', "$pattern/{id}/edit", "$controller@update");
        static::match('GET|HEAD', "$pattern/{id}/edit", "$controller@edit");
        static::match('GET|HEAD', "$pattern/{id}", "$controller@show");
    }

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
            $args = '?';

            foreach ($data as $key => $value) {
                $args .= "$key=$value&";
            }

            $data = rtrim($args, '&');
        }

        header("location: $route$data");
    }
}
