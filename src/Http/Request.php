<?php

namespace Leaf\Http;

/**
 * Leaf HTTP Request
 *
 * This class provides a human-friendly interface to the Leaf environment variables;
 * environment variables are passed by reference and will be modified directly.
 *
 * @author  Michael Darko
 * @since   1.0.0
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
    protected static $formDataMediaTypes = array('application/x-www-form-urlencoded');

    /**
     * Application Environment
     * @var \Leaf\Environment
     */
    protected $env;

    /**
     * HTTP Headers
     * @var \Leaf\Http\Headers
     */
    public $headers;

    /**
     * HTTP Cookies
     * @var \Leaf\Helpers\Set
     */
    public $cookies;

    /**
     * The Request Body
     */
    protected $request;

    public function __construct()
    {
        $this->env = new \Leaf\Environment();
        $handler = fopen('php://input', 'r');
        $this->request = stream_get_contents($handler);
        $this->headers = new Headers();
    }

    /**
     * Get HTTP method
     * @return string
     */
    public function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Check for request method type
     * 
     * @param string $type The type of request to check for
     * @return bool
     */
    public function typeIs(string $type)
    {
        return $this->getMethod() === strtoupper($type);
    }

    /**
     * Find if request has a particular header
     * 
     * @param string $header  Header to check for
     * @return bool
     */
    public function hasHeader(String $header)
    {
        if ($this->headers->get($header)) return true;
        return false;
    }

    /**
     * Is this an AJAX request?
     * @return bool
     */
    public function isAjax()
    {
        if ($this->params('isajax')) return true;
        if ($this->headers->get('X_REQUESTED_WITH') && $this->headers->get('X_REQUESTED_WITH') === 'XMLHttpRequest') return true;

        return false;
    }

    /**
     * Is this an XHR request? (alias of Leaf_Http_Request::isAjax)
     * @return bool
     */
    public function isXhr()
    {
        return $this->isAjax();
    }

    /**
     * Fetch GET and POST data
     *
     * This method returns a union of GET and POST data as a key-value array, or the value
     * of the array key if requested; if the array key does not exist, NULL is returned,
     * unless there is a default value specified.
     *
     * @param  string           $key
     * @param  mixed            $default
     * @return array|mixed|null
     */
    public function params($key = null, $default = null)
    {
        $union = $this->body();
        if ($key) return isset($union[$key]) ? $union[$key] : $default;

        return $union;
    }

    /**
     * Returns request data
     *
     * This methods returns data passed into the request (request or form data). 
     * This method returns get, post, put patch, delete or raw faw form data or NULL 
     * if the data isn't found.
     *
     * @param string|array $params The parameter(s) to return
     * @param bool $safeData Sanitize output
     */
    public function get($params, bool $safeData = true)
    {
        if (is_string($params)) return $this->body($safeData)[$params] ?? null;

        $data = [];
        foreach ($params as $param) {
            $data[$param] = $this->get($param, $safeData);
        }
        return $data;
    }

    /**
     * Get all the request data as an associative array
     * 
     * @param bool $safeData Sanitize output
     */
    public function body(bool $safeData = true)
    {
        $req = is_array(json_decode($this->request, true)) ? json_decode($this->request, true) : [];
        return $safeData ? \Leaf\Util::sanitize(array_merge($_GET, $_FILES, $_POST, $req)) : array_merge($_GET, $_FILES, $_POST, $req);
    }

    /**
     * Get all files passed into the request.
     * 
     * @param string|array $filenames The file(s) you want to get
     */
    public function files($filenames = null) {
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
     * @param  string            $key
     * @return array|string|null
     */
    public function cookies($key = null)
    {
        return $key ? \Leaf\Http\Cookie::get($key) : \Leaf\Http\Cookie::all();
    }

    /**
     * Does the Request body contain parsed form data?
     * @return bool
     */
    public function isFormData()
    {
        $method = $this->env['leaf.method_override.original_method'] ?? $this->getMethod();

        return ($method === self::METHOD_POST && is_null($this->getContentType())) || in_array($this->getMediaType(), self::$formDataMediaTypes);
    }

    /**
     * Get Headers
     *
     * This method returns a key-value array of headers sent in the HTTP request, or
     * the value of a hash key if requested; if the array key does not exist, NULL is returned.
     *
     * @param string|array $key The header(s) to return
     * @param bool  $safeData Attempt to sanitize headers
     * 
     * @return mixed
     */
    public function headers($key = null, $safeData = true)
    {
        if ($key) return $this->headers->get($key, $safeData);
        return $this->headers->all($safeData);
    }

    /**
     * Get Content Type
     * @return string|null
     */
    public function getContentType()
    {
        return $this->headers->get('CONTENT_TYPE');
    }

    /**
     * Get Media Type (type/subtype within Content Type header)
     * @return string|null
     */
    public function getMediaType()
    {
        $contentType = $this->getContentType();
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
    public function getMediaTypeParams()
    {
        $contentType = $this->getContentType();
        $contentTypeParams = array();
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
    public function getContentCharset()
    {
        $mediaTypeParams = $this->getMediaTypeParams();
        if (isset($mediaTypeParams['charset'])) {
            return $mediaTypeParams['charset'];
        }

        return null;
    }

    /**
     * Get Content-Length
     * @return int
     */
    public function getContentLength()
    {
        return $this->headers->get('CONTENT_LENGTH') ?? 0;
    }

    /**
     * Get Host
     * @return string
     */
    public function getHost()
    {
        if (isset($this->env['HTTP_HOST'])) {
            if (preg_match('/^(\[[a-fA-F0-9:.]+\])(:\d+)?\z/', $this->env['HTTP_HOST'], $matches)) {
                return $matches[1];
            } else {
                if (strpos($this->env['HTTP_HOST'], ':') !== false) {
                    $hostParts = explode(':', $this->env['HTTP_HOST']);

                    return $hostParts[0];
                }
            }

            return $this->env['HTTP_HOST'];
        }

        return $this->env['SERVER_NAME'];
    }

    /**
     * Get Host with Port
     * @return string
     */
    public function getHostWithPort()
    {
        return sprintf('%s:%s', $this->getHost(), $this->getPort());
    }

    /**
     * Get Port
     * @return int
     */
    public function getPort()
    {
        return (int) $this->env['SERVER_PORT'];
    }

    /**
     * Get Scheme (https or http)
     * @return string
     */
    public function getScheme()
    {
        return $this->env['leaf.url_scheme'];
    }

    /**
     * Get Script Name (physical path)
     * @return string
     */
    public function getScriptName()
    {
        return $this->env['SCRIPT_NAME'];
    }

    /**
     * LEGACY: Get Root URI (alias for Leaf_Http_Request::getScriptName)
     * @return string
     */
    public function getRootUri()
    {
        return $this->getScriptName();
    }

    /**
     * Get Path (physical path + virtual path)
     * @return string
     */
    public function getPath()
    {
        return $this->getScriptName() . $this->getPathInfo();
    }

    /**
     * Get Path Info (virtual path)
     * @return string
     */
    public function getPathInfo()
    {
        return $this->env['PATH_INFO'];
    }

    /**
     * LEGACY: Get Resource URI (alias for Leaf_Http_Request::getPathInfo)
     * @return string
     */
    public function getResourceUri()
    {
        return $this->getPathInfo();
    }

    /**
     * Get URL (scheme + host [ + port if non-standard ])
     * @return string
     */
    public function getUrl()
    {
        $url = $this->getScheme() . '://' . $this->getHost();
        if (($this->getScheme() === 'https' && $this->getPort() !== 443) || ($this->getScheme() === 'http' && $this->getPort() !== 80)) {
            $url .= sprintf(':%s', $this->getPort());
        }

        return $url;
    }

    /**
     * Get IP
     * @return string
     */
    public function getIp()
    {
        $keys = array('X_FORWARDED_FOR', 'HTTP_X_FORWARDED_FOR', 'CLIENT_IP', 'REMOTE_ADDR');
        foreach ($keys as $key) {
            if (isset($this->env[$key])) {
                return $this->env[$key];
            }
        }

        return $this->env['REMOTE_ADDR'];
    }

    /**
     * Get Referrer
     * @return string|null
     */
    public function getReferrer()
    {
        return $this->headers->get('HTTP_REFERER');
    }

    /**
     * Get Referer (for those who can't spell)
     * @return string|null
     */
    public function getReferer()
    {
        return $this->getReferrer();
    }

    /**
     * Get User Agent
     * @return string|null
     */
    public function getUserAgent()
    {
        return $this->headers->get('HTTP_USER_AGENT');
    }
}
