<?php

namespace Leaf\Http;

/**
 * HTTP Headers
 * ---------------------
 * Response header management made simple with Leaf
 *
 * @author Michael Darko
 * @since 2.0.0
 */
class Headers
{
    protected static $http_code;

    /**
     * Get or Set an HTTP code for response
     *
     * @param int|null $http_code The current response code.
     */
    public static function status($http_code = null)
    {
        if (!$http_code) return self::$http_code;
        self::$http_code = $http_code;
    }

    /**
     * Force an HTTP code for response using PHP's `http_response_code`
     */
    public static function resetStatus($http_code = null)
    {
        return http_response_code($http_code);
    }

    /**
     * Get all headers passed into application
     *
     * @param bool $safeOutput Try to sanitize header data
     */
    public static function all($safeOutput = false): array
    {
        if ($safeOutput === false) return self::findHeaders();
        return \Leaf\Util::sanitize(self::findHeaders());
    }

    /**
     * Return a particular header passed into app
     *
     * @param string|array $param The header(s) to return
     * @param bool $safeOutput Try to sanitize header data
     *
     * @return string|array
     */
    public static function get($params, $safeOutput = false)
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
    public static function set($key, $value = "", $replace = true, $http_code = null): void
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

    public static function contentPlain($code = null): void
    {
        self::set("Content-Type", "text/plain", true, $code ?? self::$http_code);
    }

    public static function contentHtml($code = null): void
    {
        self::set("Content-Type", "text/html", true, $code ?? self::$http_code);
    }

    public static function contentJSON($code = null): void
    {
        self::set("Content-Type", "application/json", true, $code ?? self::$http_code);
    }

    public static function accessControl($key, $value = "", $code = null)
    {
        if (is_string($key)) {
            self::set("Access-Control-$key", $value, true, $code ?? self::$http_code);
        } else {
            foreach ($key as $header => $header_value) {
                self::accessControl($header, $header_value, $code);
            }
        }
    }

    protected static function findHeaders()
    {
        if (function_exists("getallheaders") && \getallheaders()) return \getallheaders();

        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if ((substr($name, 0, 5) == 'HTTP_') || ($name == 'CONTENT_TYPE') || ($name == 'CONTENT_LENGTH')) {
                $headers[str_replace([' ', 'Http'], ['-', 'HTTP'], ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
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
     * @param int $time The last modified UNIX timestamp
     */
    public static function lastModified(int $time)
    {
        Headers::set('Last-Modified', gmdate('D, d M Y H:i:s T', $time));

        if ($time === strtotime(Headers::get('If-Modified-Since'))) {
            \Leaf\App::halt(304);
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
     * @param string $value The etag value
     * @param string $type The type of etag to create; either "strong" or "weak"
     */
    public static function etag(string $value, string $type = "strong")
    {
        if (!in_array($type, ["strong", "weak"])) {
            trigger_error("Invalid Leaf::etag type. Expected either \"strong\" or \"weak\".");
        }

        $value = "\"$value\"";

        if ($type === "weak") {
            $value = "W/" . $value;
        }

        Headers::set("ETag", $value);

        if ($etagsHeader = Headers::get("If-None-Match")) {
            $etags = preg_split("@\s*,\s*@", $etagsHeader);

            if (in_array($value, $etags) || in_array("*", $etags)) {
                $_304Methods = [Request::METHOD_GET, Request::METHOD_HEAD];

                if (in_array(Request::getMethod(), $_304Methods)) {
                    \Leaf\App::halt(304);
                } else {
                    // according to https://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.26
                    // all methods besides GET and HEAD should return a 421 (Precondition Failed)
                    \Leaf\App::halt(412);
                }
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
     * @param string|int $time If string, a time to be parsed by `strtotime()`;  If int, a UNIX timestamp;
     */
    public static function expires($time)
    {
        if (is_string($time)) {
            $time = strtotime($time);
        }

        Headers::set('Expires', gmdate('D, d M Y H:i:s T', $time));
    }
}
