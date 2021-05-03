<?php

namespace Leaf;

/**
 * Bare UI
 * ------------
 * Leaf templating language focused on speed, speed and more speed.
 * 
 * @since v2.4.4
 * @author Michael Darko <mickdd22@gmail.com>
 */
class BareUI
{
    private static $config = [
        "path" => null,
        "params" => [],
    ];

    /**
     * Configure bare UI
     * 
     * @param array|string $item The item(s) to configure
     * @param mixed $value Value of config. Ignored if $item is array.
     */
    public static function config($item, $value = null)
    {
        if (is_string($item)) {
            static::$config[$item] = $value;
        } else {
            static::$config = array_merge(static::$config, $item);
        }
    }

    /**
     * Render a bare UI
     * 
     * @param string $view The view to render
     * @param array $data The params to pass into UI
     */
    public static function render(string $view, array $data = [])
    {
        $view = static::getView($view);

        if (!file_exists($file = (static::$config["path"] ?? Config::get("views.path")) . "/$view")) {
            trigger_error("The file $view could not be found.");
        }

        extract(array_merge($data, ['template' => self::class]));

        ob_start();

        try {
            include($file);
        } catch (\Throwable $th) {
            trigger_error($th);
        }

        return (ob_get_clean());
    }

    private static function getView($view)
    {
        if (!strpos($view, ".view.php")) {
            $view .= ".view.php";
        }

        return $view;
    }
}
