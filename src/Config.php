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
    protected static $settings = [
        'app' => ['down' => false, 'instance' => null],
        'mode' => 'development',
        'debug' => true,
        'log' => [
            'writer' => null,
            'level' => null,
            'enabled' => false,
            'dir' => __DIR__ . '/../../../../storage/logs/',
            'file' => 'log.txt',
            'open' => true,
        ],
        'http' => ['version' => '1.1'],
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
        if (is_string($item)) {
            if (!strpos($item, '.')) {
                static::$settings[$item] = $value;
            } else {
                static::$settings = array_merge(
                    static::$settings,
                    static::mapConfig($item, $value)
                );
            }
        } else {
            foreach ($item as $k => $v) {
                static::set($k, $v);
            }
        }
    }

    /**
     * Map nested config to their parents recursively
     */
    protected static function mapConfig(string $item, $value = null)
    {
        $config = explode('.', $item);

        if (count($config) > 2) {
            trigger_error('Nested config can\'t be more than 1 level deep');
        }

        return [$config[0] => array_merge(
            static::$settings[$config[0]] ?? [],
            [$config[1] => $value]
        )];
    }

    /**
     * Get configuration
     *
     * @param string|null $item The config to get. Returns all items if nothing is specified.
     */
    public static function get($item = null)
    {
        if ($item) {
            $items = explode('.', $item);

            if (count($items) > 1) {
                return static::$settings[$items[0]][$items[1]] ?? null;
            }

            return static::$settings[$item] ?? null;
        }

        return static::$settings;
    }
}
