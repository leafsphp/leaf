<?php

namespace Leaf\Http;

/**
 * Leaf Http Caching
 * ------------------------------------
 * HTTP Caching made simple with Leaf
 * 
 * @author Michael Darko
 * @since  3.0.0
 */
class Cache
{
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
