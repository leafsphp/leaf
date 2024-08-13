<?php

declare(strict_types=1);

namespace Leaf;

/**
 * Leaf Config
 * -------------
 * Configure your leaf app
 */
class Config
{
    /**
     * Leaf application context
     * @var array
     */
    protected static $context = [
        'app' => ['down' => false, 'instance' => null],
        'debug' => true,
        'eien' => ['enabled' => true],
        'http' => ['version' => '1.1'],
        'log' => [
            'writer' => null,
            'level' => null,
            'enabled' => false,
            'dir' => __DIR__ . '/../../../../storage/logs/',
            'file' => 'log.txt',
            'open' => true,
        ],
        'mode' => 'development',
        'scripts' => [],
        'views' => ['path' => null, 'cachePath' => null],
    ];

    /**
     * Set configuration value(s)
     *
     * @param string|array $item The config(s) to set
     * @param mixed $value The value for config. Ignored if $item is an array.
     */
    public static function set($item, $value = null)
    {
        if (\is_string($item)) {
            if (!\strpos($item, '.')) {
                static::$context[$item] = $value;
            } else {
                static::$context = \array_merge(
                    static::$context,
                    static::mapContext($item, $value)
                );
            }
        } else {
            foreach ($item as $k => $v) {
                static::set($k, $v);
            }
        }
    }

    /**
     * Grab context
     *
     * @param string|null $item The config to get. Returns all items if nothing is specified.
     */
    public static function get($item = null)
    {
        if (!$item) {
            return static::$context;
        }

        $value = \Leaf\Anchor::deepGetDot(static::$context, $item);

        return static::isInvokable($value) ? $value(static::$context) : $value;
    }

    /**
     * Static getter
     * @param  string $item The item to get
     */
    public static function getStatic($item = null)
    {
        return static::$context[$item] ?? null;
    }

    /**
     * Get data value with key
     * @param  string $value   The item to check
     */
    public static function isInvokable($value)
    {
        return is_object($value) && method_exists($value, '__invoke');
    }

    /**
     * IteratorAggregate
     */
    public static function getIterator()
    {
        return new \ArrayIterator(static::$context);
    }

    /**
     * Ensure a value or object will remain globally unique
     *
     * @param  string   $key   The value or object name
     * @param  \Closure $value The closure that defines the object
     *
     * @return mixed
     */
    public static function singleton($key, $value)
    {
        static::set($key, function ($c) use ($value) {
            static $object;

            if (null === $object) {
                $object = $value($c);
            }

            return $object;
        });
    }

    /**
     * Protect closure from being directly invoked
     * @param  \Closure $callable A closure to keep from being invoked and evaluated
     * @return \Closure
     */
    public static function protect(\Closure $callable)
    {
        return function () use ($callable) {
            return $callable;
        };
    }

    /**
     * Add a script to attach to the leaf instance
     * @param callable $script The script to attach
     */
    public static function addScript(callable $script)
    {
        static::$context['scripts'][] = $script;
    }

    /**
     * Fetch set data keys
     * @return array This set's key-value data array keys
     */
    public static function keys()
    {
        return array_keys(static::$context);
    }

    /**
     * Does this set contain a key?
     * @param  string  $key The data key
     * @return bool
     */
    public static function has($key)
    {
        return array_key_exists($key, static::$context);
    }

    /**
     * Remove value with key from this set
     * @param  string $key The data key
     */
    public static function remove($key)
    {
        unset(static::$context[$key]);
    }

    /**
     * Countable
     */
    public static function count()
    {
        return count(static::$context);
    }

    /**
     * Clear all values
     */
    public static function reset()
    {
        static::$context = [
            'app' => static::$context['app'],
            'debug' => static::$context['debug'],
            'eien' => static::$context['eien'],
            'http' => static::$context['http'],
            'log' => static::$context['log'],
            'mode' => static::$context['mode'],
            'scripts' => [],
            'views' => static::$context['views'],
        ];
    }

    /**
     * Map nested config to their parents recursively
     */
    protected static function mapContext(string $item, $value = null)
    {
        $config = \explode('.', $item);

        if (\count($config) > 2) {
            \trigger_error('Nested config can\'t be more than 1 level deep');
        }

        return [$config[0] => \array_merge(
            static::$context[$config[0]] ?? [],
            [$config[1] => $value]
        )];
    }

    /**
     * Property Overloading
     */

    public static function __callstatic($key, $arguments)
    {
        return static::get($key);
    }
}
