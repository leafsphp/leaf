<?php

/*-
 * Copyright © 2011–2014 Federico Ulfo and a lot of awesome contributors
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * “Software”), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Leaf\Veins\Template;

/**
 * Interface for a plugin classes for rain template engine.
 * Plugins should at first declare implemented hooks during registration.
 *
 * Example implementation:
 * <code>
 * public function declare_hooks() {
 *   return array('before_parse', 'after_parse' => 'custom_method');
 * }
 * </code>
 *
 * Template will then call the registered method with a context object as parameter.
 * Context object implements \ArrayAccess.
 * It's properties depends on hook api.
 *
 * Method can modify some properties. No return value is expected.
 */
interface IPlugin
{
    /**
     * Returns a list of hooks that are implemented by the plugin.
     * This should be an array containing:
     * - a key/value pair where key is hook name and value is implementing method,
     * - a value only when hook has same name as method.
     */
    public function declareHooks();

    /**
     * Sets plugin options.
     *
     * @var array
     */
    public function setOptions($options);
}
