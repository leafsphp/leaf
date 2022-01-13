<?php

declare(strict_types=1);

namespace Leaf\Exception;

use Leaf\Http\Response;

/**
 * Stop Exception
 *
 * This is a general exception thrown from the leaf app
 *
 * @author Michael Darko
 * @since 2.0.0
 */
class General extends \Exception
{
    protected $response;

    protected $config = [];

    public function __construct($throwable)
    {
        $this->response = new Response();
        $this->handleException($throwable);
    }

    /**
     * Configure exception handler
     */
    public function configure($config)
    {
        $configuration = array_merge($this->config, $config);
        $this->config = $configuration;
    }

    /**
     * Handles an exception
     */
    protected function handleException($throwable)
    {
        $this->response->throwErr($throwable);
    }

    /**
     * Convert errors into ErrorException objects
     *
     * This method catches PHP errors and converts them into \ErrorException objects;
     * these \ErrorException objects are then thrown and caught by Leaf's
     * built-in or custom error handlers.
     *
     * @param  int $errno   The numeric type of the Error
     * @param  string $errstr  The error message
     * @param  string $errfile The absolute path to the affected file
     * @param  int $errline The line number of the error in the affected file
     * @return bool
     * @throws \ErrorException
     */
    public static function handleErrors($errno, $errstr = '', $errfile = '', $errline = '')
    {
        if (!($errno & error_reporting())) {
            return;
        }

        try {
            throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
        } catch (\Throwable $th) {
            $app = \Leaf\Config::get("app")["instance"];

            if ($app && $app->config("log.enabled")) {
                $app->logger()->error($th);
            }

            exit(static::renderBody($th));
        }
    }

    /**
     * Returns ErrorException objects from errors
     *
     * This method catches PHP errors and converts them into \ErrorException objects;
     * these \ErrorException objects are then thrown and caught by Leaf's
     * built-in or custom error handlers.
     *
     * @param  int $errno   The numeric type of the Error
     * @param  string $errstr  The error message
     * @param  string $errfile The absolute path to the affected file
     * @param  int $errline The line number of the error in the affected file
     * @return void|\ErrorException
     */
    public static function toException($errno, $errstr = '', $errfile = '', $errline = '')
    {
        if (!($errno & error_reporting())) {
            return;
        }

        try {
            throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
        } catch (\Throwable $th) {
            return $th;
        }
    }

    /**
     * Render response body
     *
     * @param array $env
     * @param \Exception $exception
     *
     * @return string
     */
    protected static function renderBody($exception)
    {
        $title = static::$config['ERROR_TITLE'] ?? 'Leaf Application Error';
        $code = $exception->getCode();
        $message = htmlspecialchars($exception->getMessage());
        $file = $exception->getFile();
        $line = $exception->getLine();

        $trace = str_replace(
            ['#', "\n"],
            ['<div>#', '</div>'],
            htmlspecialchars($exception->getTraceAsString())
        );
        $trace = str_replace(['): ', '</div>'], ['): <span style="color:#f4ae5d;">', '</span></div>'], $trace);
        $body = "<h1 style=\"color:#34be6d;\">$title</h1>";
        $body .= '<p>The application could not run because of the following error:</p>';
        $body .= '<h2>Details</h2>';
        $body .= sprintf('<div><strong>Type:</strong> %s</div>', get_class($exception));

        if ($code) {
            $body .= "<div><strong>Code:</strong> $code</div>";
        }

        if ($message) {
            $body .= "<div><strong>Message:</strong> $message</div>";
        }

        if ($file) {
            $body .= "<div><strong>File:</strong> $file</div>";
        }

        if ($line) {
            $body .= "<div><strong>Line:</strong> $line</div>";
        }

        if ($trace) {
            $body .= '<h2>Trace</h2>';
            $body .= "<pre style=\"padding:20px 30px 15px 30px;background:#003543;overflow-x:scroll;border-radius:10px;\">$trace</pre>";
        }

        return static::exceptionMarkup($title, $body);
    }

    /**
     * Generate diagnostic template markup
     *
     * This method accepts a title and body content to generate an HTML document layout.
     *
     * @param  string $title The title of the HTML template
     * @param  string $body The body content of the HTML template
     * @return string
     */
    protected static function errorMarkup($title, $body)
    {
        return "<html><head><title>$title</title><link rel=\"stylesheet\" href=\"https://fonts.googleapis.com/css?family=DM+Sans:ital,wght@0,400;0,500;0,700;1,400;1,500;1,700;display=swap\"><style>body{display:flex;justify-content:center;align-items:center;background-color:rgb(0,30,38);color:white;margin:0;padding:0px 30px;font-family:DM Sans,sans-serif;}h1{margin:0;font-weight:normal;}strong{display:inline-block;width:65px;}</style></head><body><h1 style=\"color: #34be6d;border-right:1px solid #555855;padding-right:20px;\">$title</h1><main style=\"padding-left:20px;\">$body</main></body></html>";
    }

    /**
     * Generate diagnostic template markup
     *
     * This method accepts a title and body content to generate an HTML document layout.
     *
     * @param  string $title The title of the HTML template
     * @param  string $body The body content of the HTML template
     * @return string
     */
    protected static function exceptionMarkup($title, $body)
    {
        return "<html><head><title>$title</title><link rel=\"stylesheet\" href=\"https://fonts.googleapis.com/css?family=DM+Sans:ital,wght@0,400;0,500;0,700;1,400;1,500;1,700;display=swap\"><style>body{background-color:rgb(0,30,38);color:white;margin:0;padding:50px;font:15px/14px DM Sans,sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}h2{margin-top:70px;}strong{color:#34be6d;display:inline-block;width:65px;}div{margin:15px 0px;}div strong{margin-right:40px;}</style></head><body>$body</body></html>";
    }

    /**
     * Default Not Found handler
     */
    public static function defaultDown()
    {
        echo static::errorMarkup(
            'Oops!',
            '<p>App is under maintainance, please check back soon.</p>'
        );
    }

    /**
     * Default Not Found handler
     */
    public static function default404()
    {
        echo static::errorMarkup(
            '404',
            '<p>The page you are looking for could not be found.</p>'
        );
    }

    /**
     * CSRF error
     */
    public static function csrf($error = null)
    {
        echo static::errorMarkup(
            'Invalid request',
            "<p>$error</p>" ?? '<p>The page you are looking for has expired.</p>'
        );
    }

    /**
     * Default Error handler
     */
    public static function defaultError($e = null)
    {
        if ($e) {
            $app = \Leaf\Config::get("app")["instance"];

            if ($app && $app->config("log.enabled")) {
                $app->logger()->error($e);
            }
        }

        echo self::errorMarkup('Oops!', '<p>A website error has occurred, our team has been notified.</p>');
    }
}
