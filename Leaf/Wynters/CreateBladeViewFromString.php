<?php

namespace Leaf\Wynter;

use Illuminate\Container\Container;

class CreateBladeViewFromString
{
    public function __invoke($contents)
    {
        return $this->createBladeViewFromString(/*app('view')*/ new \Leaf\Blade(), $contents);
    }

    /**
     * Create a Blade view with the raw component string content.
     *
     * @param  \Illuminate\Contracts\View\Factory  $factory
     * @param  string  $contents
     * @return string
     */
    protected function createBladeViewFromString($factory, $contents)
    {
        $factory->addNamespace(
            '__components',
            $directory = Container::getInstance()['config']->get('view.compiled')
        );

        if (!file_exists($viewFile = $directory . '/' . sha1($contents) . '.blade.php')) {
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            
            file_put_contents($viewFile, $contents);
        }
        \Leaf\JS\Scripts::c_log("CBVFS", json_encode($directory));

        return '__components::' . basename($viewFile, '.blade.php');
    }

    public function render()
    {
        //
    }
}
