<?php

namespace Leaf\Middleware;

/**
 * Pretty Exceptions
 *
 * This middleware catches any Exception thrown by the surrounded
 * application and displays a developer-friendly diagnostic screen.
 *
 * @package Leaf
 * @author  Michael Darko
 * @since   1.0.0
 */
class PrettyExceptions extends \Leaf\Middleware
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * Constructor
     * @param array $settings
     */
    public function __construct($settings = [])
    {
        $this->settings = $settings;
    }

    /**
     * Call
     */
    public function call()
    {
        try {
            $this->next->call();
        } catch (\Exception $e) {
            // $log = $this->app->getLog(); // Force Leaf to append log to env if not already
            // $env = $this->app->environment();
            // $env['leaf.log'] = $log;
            // $env['leaf.log']->error($e);
            $this->app->response()->headers->contentHtml(500);
            exit($this->app->response()->markup($this->renderBody($e)));
        }
    }

    /**
     * Render response body
     * @param  array      $env
     * @param  \Exception $exception
     * @return string
     */
    protected function renderBody($exception)
    {
        $title = 'Leaf Application Error';
        $code = $exception->getCode();
        $message = htmlspecialchars($exception->getMessage());
        $file = $exception->getFile();
        $line = $exception->getLine();
        $trace = str_replace(array('#', "\n"), array('<div>#', '</div>'), htmlspecialchars($exception->getTraceAsString()));
        $html = sprintf('<h1 style="color:#038f03;">%s</h1>', $title);
        $html .= '<p>The application could not run because of the following error:</p>';
        $html .= '<h2>Details</h2>';
        $html .= sprintf('<div><strong>Type:</strong> %s</div>', get_class($exception));
        if ($code) {
            $html .= sprintf('<div><strong>Code:</strong> %s</div>', $code);
        }
        if ($message) {
            $html .= sprintf('<div><strong>Message:</strong> %s</div>', $message);
        }
        if ($file) {
            $html .= sprintf('<div><strong>File:</strong> %s</div>', $file);
        }
        if ($line) {
            $html .= sprintf('<div><strong>Line:</strong> %s</div>', $line);
        }
        if ($trace) {
            $html .= '<h2>Trace</h2>';
            $html .= sprintf('<pre style="padding:20px;background:#ddd;overflow-x:scroll;">%s</pre>', $trace);
        }

        return sprintf("<html><head><title>%s</title><style>body{margin:0;padding:40px;font:12px/1.5 Helvetica,Arial,Verdana,sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{color:#038f03;display:inline-block;width:65px;}</style></head><body>%s</body></html>", $title, $html);
    }
}
