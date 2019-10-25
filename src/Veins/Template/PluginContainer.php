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
 * Maintains template plugins and call hook methods.
 */
class PluginContainer
{
    /**
     * Hook callables sorted by hook name.
     *
     * @var array
     */
    private $hooks = array();

    /**
     * Registered plugin instances sorted by name.
     *
     * @var array
     */
    private $plugins = array();

    /**
     * Safe method that will not override plugin of same name.
     * Instead an exception is thrown.
     *
     * @param string $name
     * @param IPlugin $plugin
     * @throws \InvalidArgumentException Plugin of same name already exists in container.
     * @return PluginContainer
     */
    public function addPlugin($name, IPlugin $plugin) {
        if (isset($this->plugins[(string) $name])) {
            throw new \InvalidArgumentException('Plugin named "' . $name . '" already exists in container');
        }
        return $this->setPlugin($name, $plugin);
    }

    /**
     * Sets plugin by name. Plugin of same name is replaced when exists.
     *
     * @param string $name
     * @param IPlugin $plugin
     * @return PluginContainer
     */
    public function setPlugin($name, IPlugin $plugin) {
        $this->removePlugin($name);
        $this->plugins[(string) $name] = $plugin;

        foreach ((array) $plugin->declareHooks() as $hook => $method) {
            if (is_int($hook)) {
                // numerical key, method has same name as hook
                $hook = $method;
            }
            $callable = array($plugin, $method);
            if (!is_callable($callable)) {
                throw new \InvalidArgumentException(sprintf(
                    'Wrong callcable suplied by %s for "%s" hook ',
                    get_class($plugin), $hook
                ));
            }
            $this->hooks[$hook][] = $callable;
        }
        return $this;
    }

    public function removePlugin($name) {
        $name = (string) $name;
        if (!isset($this->plugins[$name])) {
            return;
        }
        $plugin = $this->plugins[$name];
        unset($this->plugins[$name]);
        // remove all registered callables
        foreach ($this->hooks as $hook => &$callables) {
            foreach ($callables as $i => $callable) {
                if ($callable[0] === $plugin) {
                    unset($callables[$i]);
                }
            }
        }
        return $this;
    }

    /**
     * Passes the context object to registered plugins.
     *
     * @param string $hook_name
     * @param \ArrayAccess $context
     * @return PluginContainer
     */
    public function run($hook_name, \ArrayAccess $context ){
        if (!isset($this->hooks[$hook_name])) {
            return $this;
        }
        $context['_hook_name'] = $hook_name;
        foreach( $this->hooks[$hook_name] as $callable ){
            call_user_func($callable, $context);
        }
        return $this;
    }

    /**
     * Retuns context object that will be passed to plugins.
     *
     * @param array $params
     * @return \ArrayObject
     */
    public function createContext($params = array())
    {
        return new \ArrayObject((array) $params, \ArrayObject::ARRAY_AS_PROPS);
    }
}
