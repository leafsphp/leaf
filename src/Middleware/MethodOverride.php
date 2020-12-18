<?php

namespace Leaf\Middleware;

/**
 * HTTP Method Override
 *
 * This is middleware for a Leaf application that allows traditional
 * desktop browsers to submit pseudo PUT and DELETE requests by relying
 * on a pre-determined request parameter. Without this middleware,
 * desktop browsers are only able to submit GET and POST requests.
 *
 * This middleware is included automatically!
 *
 * @package    Leaf
 * @author     Michael Darko
 * @since      2.0.0
 */
class MethodOverride extends \Leaf\Middleware
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * Constructor
     * @param  array  $settings
     */
    public function __construct($settings = array())
    {
        $this->settings = array_merge(array('key' => '_METHOD'), $settings);
    }

    /**
     * Call
     *
     * Implements Leaf middleware interface. This method is invoked and passed
     * an array of environment variables. This middleware inspects the environment
     * variables for the HTTP method override parameter; if found, this middleware
     * modifies the environment settings so downstream middleware and/or the Leaf
     * application will treat the request with the desired HTTP method.
     *
     * @return array[status, header, body]
     */
    public function call()
    {
        $env = $this->app->environment();
        $this->next->call();
    }
}
