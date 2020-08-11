<?php
namespace Leaf\Http;

 /**
  * HTTP Headers
  * ---------------------
  * Response header management made simple with Leaf
  *
  * @author  Michael Darko
  * @since   2.0.0
  */
class Headers
{
    protected static $http_code;

    /**
     * Set an HTTP code for response
     */
    public static function status($http_code)
    {
        self::$http_code = $http_code;
    }

    /**
     * Force an HTTP code for response using PHP's `http_response_code`
     */
    public static function resStatus($http_code = null)
    {
        return http_response_code($http_code);
    }

    /**
     * Get all headers passed into application
     * 
     * @param bool $safeOutput Try to sanitize header data
     */
    public static function all($safeOutput = true) : array
    {
        if ($safeOutput === false) return self::findHeaders();

        $headers = [];
        foreach (self::findHeaders() as $key => $value) {
            $headers[$key] = $value;
        }
        return \Leaf\Util::sanitize($headers);
    }

    /**
     * Return a particular header passed into app
     * 
     * @param string|array $param The header(s) to return
     * @param bool $safeOutput Try to sanitize header data
     * 
     * @return string|array
     */
    public static function get($params, $safeOutput = true)
    {
        if (is_string($params)) return self::all($safeOutput)[$params] ?? null;

        $data = [];
        foreach ($params as $param) {
            $data[$param] = self::get($param, $safeOutput);
        }
        return $data;
    }

    /**
     * Set a new header
     */
    public static function set($key, $value = "", $replace = true, $http_code = null) : void
    {
        if (!is_array($key)) {
            header("$key: $value", $replace, $http_code ?? self::$http_code);
        } else {
            foreach ($key as $header => $header_value) {
                self::set($header, $header_value, $replace, $http_code);
            }
        }
    }

    public static function remove($keys)
    {
        if (!is_array($keys)) {
            header_remove($keys);
        } else {
            foreach ($keys as $key) {
                self::remove($key);
            }
        }
    }

    public static function contentPlain($code = null) : void
    {
        self::set("Content-Type", "text/plain", true, $code);
    }

    public static function contentHtml($code = null): void
    {
        self::set("Content-Type", "text/html", true, $code);
    }

    public static function contentJSON($code = null) : void
    {
        self::set("Content-Type", "application/json", true, $code);
    }

    public static function accessControl($key, $value = "", $code = null)
    {
        if (is_string($key)) {
            self::set("Access-Control-$key", $value, true, $code);
        } else {
            foreach ($key as $header => $header_value) {
                self::accessControl($header, $header_value, $code);
            }
        }
    }

    protected static function findHeaders() {
        return getallheaders();

        // Method getallheaders() not available or went wrong: manually extract 'm
        foreach ($_SERVER as $name => $value) {
            if ((substr($name, 0, 5) == 'HTTP_') || ($name == 'CONTENT_TYPE') || ($name == 'CONTENT_LENGTH')) {
                $headers[str_replace([' ', 'Http'], ['-', 'HTTP'], ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}
