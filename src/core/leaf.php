<?php
    /**
     * based on https://github.com/bramus/router
     */
    namespace Leaf\Core;
    /**
     *  Leaf
     *  --------
     *  This is the core of the Leaf framework. It's just a router and a few handy functions
     */
    class Leaf
    {
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
         * Execute the router: Loop all defined before middleware's and routes, and execute the handling function if a match was found.
         *
         * @param object|callable $callback Function to be executed after a matching route was handled (= after router middleware)
         *
         * @return bool
         */
        public function run($callback = null)  {
            // Define which method we need to handle
            $this->requestedMethod = $this->getRequestMethod();
            // Handle all before middlewares
            if (isset($this->beforeRoutes[$this->requestedMethod])) {
                $this->handle($this->beforeRoutes[$this->requestedMethod]);
            }
            // Handle all routes
            $numHandled = 0;
            if (isset($this->afterRoutes[$this->requestedMethod])) {
                $numHandled = $this->handle($this->afterRoutes[$this->requestedMethod], true);
            }
            // If no route was handled, trigger the 404 (if any)
            if ($numHandled === 0) {
                if ($this->notFoundCallback) {
                    $this->invoke($this->notFoundCallback);
                } else {
                    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
                }
            } // If a route was handled, perform the finish callback (if any)
            else {
                if ($callback && is_callable($callback)) {
                    $callback();
                }
            }
            // If it originally was a HEAD request, clean up after ourselves by emptying the output buffer
            if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
                ob_end_clean();
            }
            // Return true if a route was handled, false otherwise
            return $numHandled !== 0;
        }
        /**
         * Set the 404 handling function.
         *
         * @param object|callable $fn The function to be executed
         */
        public function set404($fn) {
            $this->notFoundCallback = $fn;
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
    }
