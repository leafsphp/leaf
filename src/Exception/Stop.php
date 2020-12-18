<?php

namespace Leaf\Exception;

/**
 * Stop Exception
 *
 * This Exception is thrown when the Leaf application needs to abort
 * processing and return control flow to the outer PHP script.
 *
 * @package Leaf
 * @author  Michael Darko
 * @since   1.0.0
 */
class Stop extends \Exception
{
}
