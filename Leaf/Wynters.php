<?php

namespace Leaf;

use Exception;
use Leaf\Http\Request;
use Illuminate\Support\Fluent;
use Leaf\Wynter\Exceptions\ComponentNotFoundException;
use Leaf\Wynter\HydrationMiddleware\AddAttributesToRootTagOfHtml;
use Leaf\Wynter\Testing\TestableWynter;
use ReflectionClass;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Leaf Wynter
 * ----------------------------------
 * Wynter is a full-stack framework for PHP that makes building dynamic interfaces simple, without leaving the comfort of PHP. Wynter is built based on Caleb Porzio's [Livewire Framework](https://laravel-livewire.com/), but made available for PHP in general
 * 
 * @author  Michael Darko <mickdd22@gmail.com>
 * @version 1.0.0
 */
class Wynter
{
    use Wynter\DependencyResolverTrait;

    protected $componentAliases = [];
    protected $customComponentResolver;
    protected $hydrationMiddleware = [];
    protected $initialHydrationMiddleware = [];
    protected $initialDehydrationMiddleware = [];
    protected $listeners = [];
    protected $path;
    protected $app;
    protected $request;
    protected $files;
    protected $manifest;
    protected $manifestPath;

    public static $isWynterRequestTestingOverride;

    public function __construct(App $app, $manifestPath) {
        $this->files = new FS;
        $this->request = new Request;
        // set app to already initialised instance of Leaf\App
        $this->app = $app;
        $this->manifestPath = $manifestPath;
    }

    public function component($alias, $viewClass)
    {
        $this->componentAliases[$alias] = $viewClass;
    }

    public function componentResolver($callback)
    {
        $this->customComponentResolver = $callback;
    }

    public function getComponentClass($alias)
    {
        $class = false;

        if ($this->customComponentResolver) {
            // A developer can hijack the way Wynter finds components using Wynter::componentResolver();
            $class = call_user_func($this->customComponentResolver, $alias);
        }

        $this->component($alias, $this->getClassNames());

        // die(\Leaf\JS\Scripts::c_log($alias, $this->componentAliases));

        $class = $class ?: (
            // Let's first check if the user registered the component using:
            // Wynter::component('name', [Wynter component class]);
            // If not, we'll look in the auto-discovery manifest.
            $this->componentAliases[$alias] ?? $this->find($alias));

        // $class = $class ?: (
        //     // If none of the above worked, our last-ditch effort will be
        //     // to re-generate the auto-discovery manifest and look again.
        //     $this->build()->find($alias));

        throw_unless($class, new ComponentNotFoundException(
            "Unable to find component: [{$alias}]"
        ));

        return $this->componentAliases[$alias];
    }

    public function activate($component, $id)
    {
        $componentClass = $this->getComponentClass($component);

        throw_unless(class_exists($componentClass), new ComponentNotFoundException(
            "Component [{$component}] class not found: [{$componentClass}]"
        ));

        return new $componentClass($id);
    }

    public function mount($name, $params = [])
    {
        // This is if a user doesn't pass params, BUT passes key() as the second argument.
        if (is_string($params)) $params = [];

        $id = Str::random(20);

        $this->path = $name;

        // Allow instantiating Wynter components directly from classes.
        if (class_exists($name)) {
            $instance = new $name($id);
            // Set the name to the computed name, so that the full namespace
            // isn't leaked to the front-end.
            $name = $instance->getName();
        } else {
            $instance = $this->activate($name, $id);
        }

        $this->initialHydrate($instance, []);

        $resolvedParameters = $this->resolveClassMethodDependencies(
            $params,
            $instance,
            'mount'
        );

        $instance->mount(...$resolvedParameters);

        $dom = $instance->output();

        $response = new Fluent([
            'id' => $id,
            'name' => $name,
            'dom' => $dom,
        ]);

        $this->initialDehydrate($instance, $response);

        $response->dom = (new AddAttributesToRootTagOfHtml)($response->dom, [
            'initial-data' => array_diff_key($response->toArray(), array_flip(['dom'])),
        ]);

        $this->dispatch('mounted', $response);

        return $response;
    }

    public function dummyMount($id, $tagName)
    {
        return "<{$tagName} wire:id=\"{$id}\"></{$tagName}>";
    }

    public function find($alias)
    {
        return $this->getManifest()[$alias] ?? null;
    }

    public function getManifest()
    {
        if (!is_null($this->manifest)) {
            return $this->manifest;
        }

        if (!file_exists($this->manifestPath)) {
            $this->build();
        }

        return $this->manifest = require($this->manifestPath);
    }

    public function build()
    {
        $class = $this->getClassNames();
        $this->manifest = [(new $class('dummy-id'))->getName() => $class];

        $this->write($this->manifest);

        return $this;
    }

    protected function write(array $manifest)
    {
        if (!is_writable(dirname($this->manifestPath))) {
            throw new Exception('The ' . dirname($this->manifestPath) . ' directory must be present and writable.');
        }

        $this->files->write_file($this->manifestPath, '<?php return ' . var_export($manifest, true) . ';', true);
    }

    public function getClassNames()
    {
        return str_replace(
            ['app/', '.php', 'Controllers/', 'Wynter/', 'Components/', 'App/', '\\', '/'], 
            ['', '', '', '', '', '', '', ''], 
            $this->path
        );
        
        // $fs = [];
        // foreach ($this->files->list_dir(dirname($this->path), "*.php") as $f) {
        //     $fs[] = [$f => $f];
        // }
        // echo "<script>console.log(" . json_encode($fs) . ");</script>";
        // echo "<script>console.log(" . json_encode($this->files->list_files(dirname($this->path))) . ");</script>";
        // echo $className;
        // die(1);
    }

    public function test($name, $params = [])
    {
        return new Wynter\Testing\TestableWynter($name, $params);
    }

    public function styles($options = [])
    {
        $debug = $this->app->config('app.debug');

        $styles = $this->cssAssets();

        // HTML Label.
        $html = $debug ? ['<!-- Wynter Styles -->'] : [];

        // CSS assets.
        $html[] = $debug ? $styles : $this->minify($styles);

        return implode("\n", $html);
    }

    public function scripts($options = [])
    {
        $debug = $this->app->config('app.debug');

        $scripts = $this->javaScriptAssets($options);

        // HTML Label.
        $html = $debug ? ['<!-- Wynter Scripts -->'] : [];

        // JavaScript assets.
        $html[] = $debug ? $scripts : $this->minify($scripts);

        return implode("\n", $html);
    }

    protected function cssAssets()
    {
        return <<<HTML
<style>
    [wire\:loading] {
        display: none;
    }

    [wire\:offline] {
        display: none;
    }

    [wire\:dirty]:not(textarea):not(input):not(select) {
        display: none;
    }
</style>
HTML;
    }

    protected function javaScriptAssets($options)
    {
        $jsonEncodedOptions = $options ? json_encode($options) : '';

        $appUrl = $this->app->config('wynter.asset_url', rtrim($options['asset_url'] ?? '', '/'));
        
        session_start();
        $session_id = session_id();

        $manifest = json_decode(file_get_contents(__DIR__ . '/../dist/manifest.json'), true);
        $versionedFileName = $manifest['/livewire.js'];

        // Default to dynamic `livewire.js` (served by a Laravel route).
        $fullAssetPath = "{$appUrl}/livewire{$versionedFileName}";
        $assetWarning = null;

        // Use static assets if they have been published
        if (file_exists($this->app->config('app.path') . ('vendor/livewire'))) {
            $publishedManifest = json_decode(file_get_contents($this->app->config('app.path') . ('vendor/livewire/manifest.json')), true);
            $versionedFileName = $publishedManifest['/livewire.js'];

            $isHostedOnVapor = ($_ENV['SERVER_SOFTWARE'] ?? null) === 'vapor';

            $fullAssetPath = ($isHostedOnVapor ? $this->app->config('app.asset_url') : $appUrl) . '/vendor/livewire' . $versionedFileName;

            if ($manifest !== $publishedManifest) {
                $assetWarning = <<<'HTML'
<script>
    console.warn("Wynter: The published Wynter assets are out of date\n See: https://leafphp.nelify.com/wynter/getting-started/")
</script>
HTML;
            }
        }

        // Adding semicolons for this JavaScript is important,
        // because it will be minified in production.
        return <<<HTML
{$assetWarning}
<script src="{$fullAssetPath}" data-turbolinks-eval="false"></script>
<script data-turbolinks-eval="false">
    window.livewire = new Wynter({$jsonEncodedOptions});
    window.livewire_app_url = '{$appUrl}';
    window.livewire_token = '{$session_id}';

    /* Make Alpine wait until Wynter is finished rendering to do its thing. */
    window.deferLoadingAlpine = function (callback) {
        window.addEventListener('livewire:load', function () {
            callback();
        });
    };

    document.addEventListener("DOMContentLoaded", function () {
        window.livewire.start();
    });

    var firstTime = true;
    document.addEventListener("turbolinks:load", function() {
        /* We only want this handler to run AFTER the first load. */
        if  (firstTime) {
            firstTime = false;
            return;
        }

        window.livewire.restart();
    });

    document.addEventListener("turbolinks:before-cache", function() {
        document.querySelectorAll('[wire\\\:id]').forEach(function(el) {
            const component = el.__livewire;

            const dataObject = {
                data: component.data,
                events: component.events,
                children: component.children,
                checksum: component.checksum,
                name: component.name,
                errorBag: component.errorBag,
                redirectTo: component.redirectTo,
            };

            el.setAttribute('wire:initial-data', JSON.stringify(dataObject));
        });
    });
</script>
HTML;
    }

    protected function minify($subject)
    {
        return preg_replace('~(\v|\t|\s{2,})~m', '', $subject);
    }

    public function isWynterRequest()
    {
        if (static::$isWynterRequestTestingOverride) {
            return true;
        }

        return $this->request->hasHeader('X-Wynter');
    }

    public function registerHydrationMiddleware(array $classes)
    {
        $this->hydrationMiddleware += $classes;
    }

    public function registerInitialHydrationMiddleware(array $callables)
    {
        $this->initialHydrationMiddleware += $callables;
    }

    public function registerInitialDehydrationMiddleware(array $callables)
    {
        $this->initialDehydrationMiddleware += $callables;
    }

    public function hydrate($instance, $request)
    {
        foreach ($this->hydrationMiddleware as $class) {
            $class::hydrate($instance, $request);
        }
    }

    public function initialHydrate($instance, $request)
    {
        foreach ($this->initialHydrationMiddleware as $callable) {
            $callable($instance, $request);
        }
    }

    public function initialDehydrate($instance, $response)
    {
        foreach (array_reverse($this->initialDehydrationMiddleware) as $callable) {
            $callable($instance, $response);
        }
    }

    public function dehydrate($instance, $response)
    {
        // The array is being reversed here, so the middleware dehydrate phase order of execution is
        // the inverse of hydrate. This makes the middlewares behave like layers in a shell.
        foreach (array_reverse($this->hydrationMiddleware) as $class) {
            $class::dehydrate($instance, $response);
        }
    }

    public function getRootElementTagName($dom)
    {
        preg_match('/<([a-zA-Z0-9\-]*)/', $dom, $matches, PREG_OFFSET_CAPTURE);

        return $matches[1][0];
    }

    public function dispatch($event, ...$params)
    {
        foreach ($this->listeners[$event] ?? [] as $listener) {
            $listener(...$params);
        }
    }

    public function listen($event, $callback)
    {
        $this->listeners[$event] ?? $this->listeners[$event] = [];

        $this->listeners[$event][] = $callback;
    }

    public function isOnVapor()
    {
        return ($_ENV['SERVER_SOFTWARE'] ?? null) === 'vapor';
    }
}
