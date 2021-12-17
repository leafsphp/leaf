<?php

namespace Leaf\Http;

/**
 * Leaf HTTP Request
 * --------
 *
 * This class provides an object-oriented way to interact with the current
 * HTTP request being handled by your application as well as retrieve the input,
 * cookies, and files that were submitted with the request.
 *
 * @author Michael Darko
 * @since 1.0.0
 */
class Request
{
    const METHOD_HEAD = 'HEAD';
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_OVERRIDE = '_METHOD';

    /**
     * @var array
     */
    protected static $formDataMediaTypes = ['application/x-www-form-urlencoded'];

    /**
     * HTTP Headers
     * @var Headers
     */
    public static $headers;

    /**
     * HTTP Cookies
     */
    public static $cookies;

    /**
     * The Request Body
     */
    protected static $request;

    public function __construct()
    {
        $handler = fopen('php://input', 'r');
        static::$request = stream_get_contents($handler);
        static::$headers = new Headers();
    }

    /**
     * Get HTTP method
     * @return string
     */
    public static function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Check for request method type
     *
     * @param string $type The type of request to check for
     * @return bool
     */
    public static function typeIs(string $type): bool
    {
        return static::getMethod() === strtoupper($type);
    }

    /**
     * Find if request has a particular header
     *
     * @param string $header  Header to check for
     * @return bool
     */
    public static function hasHeader(String $header): bool
    {
        return !!static::$headers->get($header);
    }

    /**
     * Is this an AJAX request?
     * @return bool
     */
    public static function isAjax(): bool
    {
        if (static::params('isajax')) return true;
        if (static::$headers->get('X_REQUESTED_WITH') && static::$headers->get('X_REQUESTED_WITH') === 'XMLHttpRequest') return true;

        return false;
    }

    /**
     * Is this an XHR request? (alias of Leaf_Http_Request::isAjax)
     * @return bool
     */
    public static function isXhr(): bool
    {
        return static::isAjax();
    }

    /**
     * Fetch GET and POST data
     *
     * This method returns a union of GET and POST data as a key-value array, or the value
     * of the array key if requested. If the array key does not exist, NULL is returned,
     * unless there is a default value specified.
     *
     * @param string|null $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    public static function params(string $key = null, mixed $default = null): mixed
    {
        $union = static::body();

        if ($key) return $union[$key] ?? $default;

        return $union;
    }

    /**
     * Attempt to retrieve data from the request.
     *
     * Data which is not found in the request parameters will
     * be completely removed instead of returning null. Use `get`
     * if you want to return null or `params` if you want to set
     * a default value.
     *
     * @param array $params The parameters to return
     * @param bool $safeData Sanitize output?
     * @param bool $noEmptyString Remove empty strings from return data?
     */
    public static function try(array $params, bool $safeData = true, bool $noEmptyString = false)
    {
        $data = static::get($params, $safeData);
        $dataKeys = array_keys($data);

        foreach ($dataKeys as $key) {
            if (!$data[$key]) {
                unset($data[$key]);
                continue;
            }

            if ($noEmptyString && !strlen($data[$key])) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * Returns request data
     *
     * This methods returns data passed into the request (request or form data).
     * This method returns get, post, put patch, delete or raw faw form data or NULL
     * if the data isn't found.
     *
     * @param array|string $params The parameter(s) to return
     * @param bool $safeData Sanitize output
     */
    public static function get($params, bool $safeData = true)
    {
        if (is_string($params)) return static::body($safeData)[$params] ?? null;

        $data = [];

        foreach ($params as $param) {
            $data[$param] = static::get($param, $safeData);
        }

        return $data;
    }

    /**
     * Get all the request data as an associative array
     *
     * @param bool $safeData Sanitize output
     */
    public static function body(bool $safeData = true)
    {
        $req = is_array(json_decode(static::$request, true)) ? json_decode(static::$request, true) : [];
        return $safeData ? \Leaf\Anchor::sanitize(array_merge($_GET, $_FILES, $_POST, $req)) : array_merge($_GET, $_FILES, $_POST, $req);
    }

    /**
     * Get all files passed into the request.
     *
     * @param array|string|null $filenames The file(s) you want to get
     */
    public static function files($filenames = null)
    {
        if ($filenames == null) return $_FILES;
        if (is_string($filenames)) return $_FILES[$filenames] ?? null;

        $files = [];
        foreach ($filenames as $filename) {
            $files[$filename] = $_FILES[$filename] ?? null;
        }
        return $files;
    }

    /**
     * Fetch COOKIE data
     *
     * This method returns a key-value array of Cookie data sent in the HTTP request, or
     * the value of a array key if requested; if the array key does not exist, NULL is returned.
     *
     * @param string|null $key
     * @return array|string|null
     */
    public static function cookies(string $key = null)
    {
        return $key ? \Leaf\Http\Cookie::get($key) : \Leaf\Http\Cookie::all();
    }

    /**
     * Does the Request body contain parsed form data?
     * @return bool
     */
    public static function isFormData(): bool
    {
        $method = static::getMethod();

        return ($method === self::METHOD_POST && is_null(static::getContentType())) || in_array(static::getMediaType(), self::$formDataMediaTypes);
    }

    /**
     * Get Headers
     *
     * This method returns a key-value array of headers sent in the HTTP request, or
     * the value of a hash key if requested; if the array key does not exist, NULL is returned.
     *
     * @param array|string|null $key The header(s) to return
     * @param bool $safeData Attempt to sanitize headers
     *
     * @return array|string|null
     */
    public static function headers($key = null, bool $safeData = true)
    {
        if ($key) return static::$headers->get($key, $safeData);
        return static::$headers->all($safeData);
    }

    /**
     * Get Content Type
     * @return string|null
     */
    public static function getContentType(): ?string
    {
        return static::$headers->get('CONTENT_TYPE');
    }

    /**
     * Get Media Type (type/subtype within Content Type header)
     * @return string|null
     */
    public static function getMediaType(): ?string
    {
        $contentType = static::getContentType();
        if ($contentType) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);

            return strtolower($contentTypeParts[0]);
        }

        return null;
    }

    /**
     * Get Media Type Params
     * @return array
     */
    public static function getMediaTypeParams(): array
    {
        $contentType = static::getContentType();
        $contentTypeParams = [];

        if ($contentType) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);
            $contentTypePartsLength = count($contentTypeParts);

            for ($i = 1; $i < $contentTypePartsLength; $i++) {
                $paramParts = explode('=', $contentTypeParts[$i]);
                $contentTypeParams[strtolower($paramParts[0])] = $paramParts[1];
            }
        }

        return $contentTypeParams;
    }

    /**
     * Get Content Charset
     * @return string|null
     */
    public static function getContentCharset(): ?string
    {
        $mediaTypeParams = static::getMediaTypeParams();
        if (isset($mediaTypeParams['charset'])) {
            return $mediaTypeParams['charset'];
        }

        return null;
    }

    /**
     * Get Content-Length
     * @return int
     */
    public static function getContentLength(): int
    {
        return static::$headers->get('CONTENT_LENGTH') ?? 0;
    }

    /**
     * Get Host
     * @return string
     */
    public static function getHost(): string
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            if (preg_match('/^(\[[a-fA-F0-9:.]+\])(:\d+)?\z/', $_SERVER['HTTP_HOST'], $matches)) {
                return $matches[1];
            } else if (strpos($_SERVER['HTTP_HOST'], ':') !== false) {
                $hostParts = explode(':', $_SERVER['HTTP_HOST']);

                return $hostParts[0];
            }

            return $_SERVER['HTTP_HOST'];
        }

        return $_SERVER['SERVER_NAME'];
    }

    /**
     * Get Host with Port
     * @return string
     */
    public static function getHostWithPort(): string
    {
        return sprintf('%s:%s', static::getHost(), static::getPort());
    }

    /**
     * Get Port
     * @return int
     */
    public static function getPort(): int
    {
        return (int) $_SERVER['SERVER_PORT'] ?? 80;
    }

    /**
     * Get Scheme (https or http)
     * @return string
     */
    public static function getScheme(): string
    {
        return empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off' ? 'http' : 'https';
    }

    /**
     * Get Script Name (physical path)
     * @return string
     */
    public static function getScriptName(): string
    {
        return $_SERVER['SCRIPT_NAME'];
    }

    /**
     * Get Path (physical path + virtual path)
     * @return string
     */
    public static function getPath(): string
    {
        return static::getScriptName() . static::getPathInfo();
    }

    /**
     * Get Path Info (virtual path)
     * @return string|null
     */
    public static function getPathInfo(): ?string
    {
        return $_SERVER['REQUEST_URI'] ?? null;
    }

    /**
     * Get URL (scheme + host [ + port if non-standard ])
     * @return string
     */
    public static function getUrl(): string
    {
        $url = static::getScheme() . '://' . static::getHost();

        if ((static::getScheme() === 'https' && static::getPort() !== 443) || (static::getScheme() === 'http' && static::getPort() !== 80)) {
            $url .= ":" . static::getPort();
        }

        return $url;
    }

    /**
     * Get IP
     * @return string
     */
    public static function getIp(): string
    {
        $keys = ['X_FORWARDED_FOR', 'HTTP_X_FORWARDED_FOR', 'CLIENT_IP', 'REMOTE_ADDR'];

        foreach ($keys as $key) {
            if (isset($_SERVER[$key])) {
                return $_SERVER[$key];
            }
        }

        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Get Referrer
     * @return string|null
     */
    public static function getReferrer(): ?string
    {
        return static::$headers->get('HTTP_REFERER');
    }

    /**
     * Get Referer (for those who can't spell)
     * @return string|null
     */
    public static function getReferer(): ?string
    {
        return static::getReferrer();
    }

    /**
     * Get User Agent
     * @return string|null
     */
    public static function getUserAgent(): ?string
    {
        return static::$headers->get('HTTP_USER_AGENT');
    }
}
