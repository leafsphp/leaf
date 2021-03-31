<?php

namespace Leaf\Http;

/**
 * Leaf HTTP Response
 * -----------
 * This is a simple abstraction over top an HTTP response. This
 * provides methods to set the HTTP status, the HTTP headers,
 * and the HTTP body.
 *
 * @author Michael Darko
 * @since 1.0.0
 */
class Response
{
    /**
     * @var int HTTP status code
     */
    protected static $status;

    /**
     * @var \Leaf\Http\Headers
     */
    public static $headers;

    /**
     * @var array HTTP response codes and messages
     */
    protected static $messages = [
        //Informational 1xx
        100 => '100 Continue',
        101 => '101 Switching Protocols',
        //Successful 2xx
        200 => '200 OK',
        201 => '201 Created',
        202 => '202 Accepted',
        203 => '203 Non-Authoritative Information',
        204 => '204 No Content',
        205 => '205 Reset Content',
        206 => '206 Partial Content',
        226 => '226 IM Used',
        //Redirection 3xx
        300 => '300 Multiple Choices',
        301 => '301 Moved Permanently',
        302 => '302 Found',
        303 => '303 See Other',
        304 => '304 Not Modified',
        305 => '305 Use Proxy',
        306 => '306 (Unused)',
        307 => '307 Temporary Redirect',
        //Client Error 4xx
        400 => '400 Bad Request',
        401 => '401 Unauthorized',
        402 => '402 Payment Required',
        403 => '403 Forbidden',
        404 => '404 Not Found',
        405 => '405 Method Not Allowed',
        406 => '406 Not Acceptable',
        407 => '407 Proxy Authentication Required',
        408 => '408 Request Timeout',
        409 => '409 Conflict',
        410 => '410 Gone',
        411 => '411 Length Required',
        412 => '412 Precondition Failed',
        413 => '413 Request Entity Too Large',
        414 => '414 Request-URI Too Long',
        415 => '415 Unsupported Media Type',
        416 => '416 Requested Range Not Satisfiable',
        417 => '417 Expectation Failed',
        418 => '418 I\'m a teapot',
        422 => '422 Unprocessable Entity',
        423 => '423 Locked',
        426 => '426 Upgrade Required',
        428 => '428 Precondition Required',
        429 => '429 Too Many Requests',
        431 => '431 Request Header Fields Too Large',
        //Server Error 5xx
        500 => '500 Internal Server Error',
        501 => '501 Not Implemented',
        502 => '502 Bad Gateway',
        503 => '503 Service Unavailable',
        504 => '504 Gateway Timeout',
        505 => '505 HTTP Version Not Supported',
        506 => '506 Variant Also Negotiates',
        510 => '510 Not Extended',
        511 => '511 Network Authentication Required'
    ];

    public function __construct()
    {
        static::$headers = Headers::class;
        Headers::contentHtml();
    }

    /**
     * Output json encoded data with an HTTP code/message
     * 
     * @param mixed $data The data to output
     * @param int $code The response status code
     * @param bool $showCode Show response code in body?
     * @param bool $useMessage Show message instead of code
     */
    public static function json($data, int $code = 200, bool $showCode = false, bool $useMessage = false)
    {
        if ($showCode) {
            $dataToPrint = ["data" => $data, "code" => $code];

            if ($useMessage) {
                $dataToPrint = ["data" => $data, "message" => isset(self::$messages[$code]) ? self::$messages[$code] : $code];
            }
        } else {
            $dataToPrint = $data;
        }

        Headers::contentJSON($code);
        echo json_encode($dataToPrint);
    }

    /**
     * Throw an error and break the application
     */
    public static function throwErr($error, int $code = 500, bool $useMessage = false)
    {
        $dataToPrint = ["error" => $error, "code" => $code];
        if ($useMessage) $dataToPrint = ["error" => $error, "message" => isset(self::$messages[$code]) ? self::$messages[$code] : $code];

        Headers::contentJSON($code);
        echo json_encode($dataToPrint);
        exit();
    }

    public static function page(string $file, int $code = null)
    {
        Headers::contentHtml($code);
        require $file;
    }

    public static function markup(String $markup, int $code = null)
    {
        Headers::contentHtml($code);
        echo <<<EOT
$markup
EOT;
    }

    public static function cors(String $allow_origin = "*", String $allow_headers = "*")
    {
        Headers::accessControl(["Allow-Origin" => $allow_origin, "Allow-Headers" => $allow_headers]);
    }

    /**
     * Get and set header
     * 
     * @param string $name Header name
     * @param string|null $value Header value
     * @return string Header value
     */
    public static function header($name, $value = null)
    {
        if (!is_null($value)) Headers::set($name, $value);
        return Headers::get($name);
    }

    /**
     * Set HTTP status code
     */
    public static function status($code = null)
    {
        return Headers::status($code);
    }

    /**
     * Set cookie
     *
     * Set a new cookie
     *
     * @param string|array $name The name of the cookie
     * @param string $value If string, the value of cookie
     * @param array $options Settings for cookie
     */
    public static function setCookie($name, $value, $options = [])
    {
        Cookie::set($name, $value, $options);
    }

    /**
     * Shorthand method of setting a cookie + value + expire time
     *
     * @param string $name The name of the cookie
     * @param string $value The value of cookie
     * @param string $expire When the cookie expires. Default: 7 days
     */
    public static function simpleCookie($name, $value, $expire = "7 days")
    {
        Cookie::simpleCookie($name, $value, $expire);
    }

    /**
     * Delete cookie
     *
     * @param string $name The name of the cookie
     */
    public static function deleteCookie($name)
    {
        Cookie::unset($name);
    }

    /**
     * Redirect
     *
     * This method prepares this response to return an HTTP Redirect response
     * to the HTTP client.
     *
     * @param string $url    The redirect destination
     * @param int    $status The redirect HTTP status code
     */
    public static function redirect($url, $status = 302)
    {
        Headers::status($status);
        Headers::set('Location', $url);
    }

    /**
     * Get message for HTTP status code
     * 
     * @param int $status
     * @return string|null
     */
    public static function getMessageForCode($status)
    {
        return isset(self::$messages[$status]) ? self::$messages[$status] : null;
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
        Headers::lastModified($time);
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
        Headers::etag($value, $type);
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
    public static function expires($time)
    {
        Headers::expires($time);
    }
}
