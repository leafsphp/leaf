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

require_once __DIR__ . '/IPlugin.php';

/**
 * Basic plugin implementation.
 * - It allows to define hooks as property.
 * - Options can be passed in constructor.
 *   When a setter set_{optionname}() exists it is used to store the option value.
 *   Otherwise \InvalidArgumentException is thrown.
 */
class Plugin implements IPlugin
{
    /**
     * This should be an array containing:
     * - a key/value pair where key is hook name and value is implementing method,
     * - a value only when hook has same name as method.
     *
     * @var array
     */
    protected $hooks = array();

    public function  __construct($options = array())
    {
        $this->setOptions($options);
    }
    /**
     * Returns a list of hooks that are implemented by the plugin.
     * This should be an array containing:
     * - a key/value pair where key is hook name and value is implementing method,
     * - a value only when hook has same name as method.
     */
    public function declareHooks() {
        return $this->hooks;
    }

    /**
     * Sets plugin options.
     *
     * @var array
     */
    public function setOptions($options) {
        foreach ((array) $options as $key => $val) {
            $this->setOption($key, $val);
        }
        return $this;
    }

    /**
     * Sets plugin option.
     *
     * @param string $name
     * @param mixed $value
     * @throws \InvalidArgumentException Wrong option name or value
     * @return Plugin
     */
    public function setOption($name, $value) {
        $method = 'set' . ucfirst($name);

        if (!\method_exists($this, $method)) {
            throw new \InvalidArgumentException('Key "' . $name . '" is not a valid settings option' );
        }
        $this->{$method}($value);
        return $this;
    }
}
