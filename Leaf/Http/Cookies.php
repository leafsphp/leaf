<?php
namespace Leaf\Http;

/**
 * Leaf Cookies
 * ------------------------------------
 * Simple Cookie management with Leaf
 */
class Cookies extends \Leaf\Helpers\Set
{
    /**
     * Default cookie settings
     * @var array
     */
    protected $defaults = [
        'value' => '',
        'domain' => null,
        'hostonly' => null,
        'path' => null,
        'expires' => null,
        'secure' => false,
        'httponly' => false,
        'samesite' => null
    ];

    /**
     * Set cookie
     *
     * The second argument may be a single scalar value, in which case
     * it will be merged with the default settings and considered the `value`
     * of the merged result.
     *
     * The second argument may also be an array containing any or all of
     * the keys shown in the default settings above. This array will be
     * merged with the defaults shown above.
     *
     * @param string $key   Cookie name
     * @param mixed  $value Cookie settings
     */
    public function set($key, $value)
    {
        if (is_array($value)) {
            $cookieSettings = array_replace($this->defaults, $value);
        } else {
            $cookieSettings = array_replace($this->defaults, array('value' => $value));
        }
        parent::set($key, $cookieSettings);
    }

    /**
     * Shorthand method of setting a cookie + value + expire time
     *
     * @param string $name    The name of the cookie
     * @param string $value   If string, the value of cookie; if array, properties for cookie including: value, expire, path, domain, secure, httponly
     * @param string $expires When the cookie expires. Default: 7 days
     */
    public function simpleCookie($name, $value, $expires = "7 days")
    {
        $cookie = $this->defaults;
        $cookie["value"] = $value;
        $cookie["expires"] = $expires;

        $this->set($name, $cookie);
    }

    /**
     * Remove cookie
     *
     * Unlike \Leaf\Helpers\Set, this will actually *set* a cookie with
     * an expiration date in the past. This expiration date will force
     * the client-side cache to remove its cookie with the given name
     * and settings.
     *
     * @param  string $key      Cookie name
     * @param  array  $settings Optional cookie settings
     */
    public function remove($key, $settings = array())
    {
        $settings['value'] = '';
        $settings['expires'] = time() - 86400;
        $this->set($key, array_replace($this->defaults, $settings));
    }
}
