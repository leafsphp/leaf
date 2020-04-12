<?php
namespace Leaf\Http;

/**
 * Leaf HTTP Request
 *
 * This class provides a human-friendly interface to the Leaf environment variables;
 * environment variables are passed by reference and will be modified directly.
 *
 * @package Leaf
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
     * The Request Method
     */
    public $requestMethod;

    /**
     * The Request Body
     */
    protected $request;

    public function __construct()
    {
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
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
     * Is this a GET request?
     * @return bool
     */
    public function isGet()
    {
        return $this->getMethod() === self::METHOD_GET;
    }

    /**
     * Is this a POST request?
     * @return bool
     */
    public function isPost()
    {
        return $this->getMethod() === self::METHOD_POST;
    }

    /**
     * Is this a PUT request?
     * @return bool
     */
    public function isPut()
    {
        return $this->getMethod() === self::METHOD_PUT;
    }

    /**
     * Is this a PATCH request?
     * @return bool
     */
    public function isPatch()
    {
        return $this->getMethod() === self::METHOD_PATCH;
    }

    /**
     * Is this a DELETE request?
     * @return bool
     */
    public function isDelete()
    {
        return $this->getMethod() === self::METHOD_DELETE;
    }

    /**
     * Is this a HEAD request?
     * @return bool
     */
    public function isHead()
    {
        return $this->getMethod() === self::METHOD_HEAD;
    }

    /**
     * Is this a OPTIONS request?
     * @return bool
     */
    public function isOptions()
    {
        return $this->getMethod() === self::METHOD_OPTIONS;
    }

    /**
     * Is this an AJAX request?
     * @return bool
     */
    public function isAjax()
    {
        if ($this->params('isajax')) {
            return true;
        } elseif (isset($this->headers['X_REQUESTED_WITH']) && $this->headers['X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            return true;
        }

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
        if ($key) {
            return isset($union[$key]) ? $union[$key] : $default;
        }

        return $union;
    }

    /**
     * Returns request data
     *
     * This methods returns data passed into the request (request or form data). 
     * This method returns get, post, put patch, delete or raw faw form data or NULL 
     * if the data isn't found.
     *
     * @param  string           $key
     */
    public function get($param) {
        if ($this->requestMethod == "POST" || $this->requestMethod == "PUT" || $this->requestMethod == "PATCH" || $this->requestMethod == "DELETE") {
            if (isset($_POST[$param])) {
                return htmlspecialchars($_POST[$param], ENT_QUOTES, 'UTF-8');
            } else {
                $data = json_decode($this->request, true);
                return isset($data[$param]) ? htmlspecialchars($data[$param], ENT_QUOTES, 'UTF-8') : null;
            }
        } else {
            return isset($_GET[$param]) ? htmlspecialchars($_GET[$param], ENT_QUOTES, 'UTF-8') : null;
        }
    }

    /**
     * Get all the response data as an associative array
     */
    public function body() {
        $data = json_decode($this->request, true);

        $body = array();

        if($this->requestMethod === "GET") {
            foreach($_GET as $key => $value) {
                $body[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
            return count($body) > 0 ? $body : null;
        }
        if ($this->requestMethod == "POST" || $this->requestMethod == "PUT" || $this->requestMethod == "PATCH" || $this->requestMethod == "DELETE") {
            if (isset($_POST) && !empty($_POST)) {
                foreach($_POST as $key => $value) {
                    $body[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                }
                return count($body) > 0 ? $body : null;
            } else {
                foreach($data as $key => $value) {
                    $body[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                }
                return count($body) > 0 ? $body : null;
            }
        }
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
        if ($key) {
            return $this->cookies->get($key);
        }

        return $this->cookies;
        // if (!isset($this->env['leaf.request.cookie_hash'])) {
        //     $cookieHeader = isset($this->env['COOKIE']) ? $this->env['COOKIE'] : '';
        //     $this->env['leaf.request.cookie_hash'] = Util::parseCookieHeader($cookieHeader);
        // }
        // if ($key) {
        //     if (isset($this->env['leaf.request.cookie_hash'][$key])) {
        //         return $this->env['leaf.request.cookie_hash'][$key];
        //     } else {
        //         return null;
        //     }
        // } else {
        //     return $this->env['leaf.request.cookie_hash'];
        // }
    }

    /**
     * Does the Request body contain parsed form data?
     * @return bool
     */
    public function isFormData()
    {
        $method = isset($this->env['leaf.method_override.original_method']) ? $this->env['leaf.method_override.original_method'] : $this->getMethod();

        return ($method === self::METHOD_POST && is_null($this->getContentType())) || in_array($this->getMediaType(), self::$formDataMediaTypes);
    }

    /**
     * Get Headers
     *
     * This method returns a key-value array of headers sent in the HTTP request, or
     * the value of a hash key if requested; if the array key does not exist, NULL is returned.
     *
     * @param  string $key
     * @param  mixed  $default The default value returned if the requested header is not available
     * @return mixed
     */
    public function headers($key = null, $default = null)
    {
        if ($key) {
            return $this->headers->get($key, $default);
        }

        return $this->headers;
        // if ($key) {
        //     $key = strtoupper($key);
        //     $key = str_replace('-', '_', $key);
        //     $key = preg_replace('@^HTTP_@', '', $key);
        //     if (isset($this->env[$key])) {
        //         return $this->env[$key];
        //     } else {
        //         return $default;
        //     }
        // } else {
        //     $headers = array();
        //     foreach ($this->env as $key => $value) {
        //         if (strpos($key, 'leaf.') !== 0) {
        //             $headers[$key] = $value;
        //         }
        //     }
        //
        //     return $headers;
        // }
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
        return $this->headers->get('CONTENT_LENGTH', 0);
    }

    /**
     * Get Host
     * @return string
     */
    public function getHost()
    {
        if (isset($this->env['HTTP_HOST'])) {
            if(preg_match('/^(\[[a-fA-F0-9:.]+\])(:\d+)?\z/', $this->env['HTTP_HOST'], $matches)) {
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
        return (int)$this->env['SERVER_PORT'];
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
