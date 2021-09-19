<?php

namespace Leaf;

/**
 * Middleware
 *
 * @package Leaf
 * @author  Michael Darko
 * @since   1.5.0
 */
abstract class Middleware
{
    /**
     * @var \Leaf\App Reference to the primary application instance
     */
    protected $app;

    /**
     * @var mixed Reference to the next downstream middleware
     */
    protected $next;

    /**
     * Set next middleware
     *
     * This method injects the next downstream middleware into
     * this middleware so that it may optionally be called
     * when appropriate.
     *
     * @param \Leaf\Middleware
     */
    final public function setNextMiddleware($nextMiddleware)
    {
        $this->next = $nextMiddleware;
    }

    /**
     * Get next middleware
     *
     * This method retrieves the next downstream middleware
     * previously injected into this middleware.
     *
     * @return \Leaf\Middleware
     */
    final public function getNextMiddleware()
    {
        return $this->next;
    }

    /**
     * Call the next middleware
     */
    final public function callNext()
    {
        $nextMiddleware = $this->next;

        if (!$nextMiddleware) {
            return;
        }

        $nextMiddleware->call();
    }

    /**
     * Call
     *
     * Perform actions specific to this middleware and optionally
     * call the next downstream middleware.
     */
    abstract public function call();
}
