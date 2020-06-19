<?php

namespace Leaf\Wynter;

use Leaf\Str;

class BladeDirectives
{
    public static function this()
    {
        return "window.wynter.find('{{ \$_instance->id }}')";
    }

    public static function wynterStyles($expression)
    {
        return "{!! \$wynter = new \Leaf\Wynter(new \Leaf\App, './manifest'); \$wynter->styles('.$expression.') !!}";
    }

    public static function wynterScripts($expression)
    {
        return "{!! \$wynter = new \Leaf\Wynter(new \Leaf\App, './manifest'); \$wynter->scripts('.$expression.') !!}";
    }

    public static function wynter($expression)
    {
        $lastArg = trim(last(explode(',', $expression)));

        if (Str::startsWith($lastArg, 'key(') && Str::endsWith($lastArg, ')')) {
            $cachedKey = Str::replaceFirst('key(', '', Str::replaceLast(')', '', $lastArg));
            $args = explode(',', $expression);
            array_pop($args);
            $expression = implode(',', $args);
        } else {
            $cachedKey = "'".Str::random(7)."'";
        }

        return <<<EOT
<?php
\$wynter = new \Leaf\Wynter(new \Leaf\App, './manifest');
if (! isset(\$_instance)) {
    \$dom = \$wynter->mount({$expression})->dom;
} else {
    \$response = \$wynter->mount({$expression});
    \$dom = \$response->dom;
    \$_instance->logRenderedChild($cachedKey, \$response->id, \Livewire\Livewire::getRootElementTagName(\$dom));
}
echo \$dom;
?>
EOT;

//         return <<<EOT
// <?php
// if (! isset(\$_instance)) {
//     \$dom = \Livewire\Livewire::mount({$expression})->dom;
// } elseif (\$_instance->childHasBeenRendered($cachedKey)) {
//     \$componentId = \$_instance->getRenderedChildComponentId($cachedKey);
//     \$componentTag = \$_instance->getRenderedChildComponentTagName($cachedKey);
//     \$dom = \Livewire\Livewire::dummyMount(\$componentId, \$componentTag);
//     \$_instance->preserveRenderedChild($cachedKey);
// } else {
//     \$response = \Livewire\Livewire::mount({$expression});
//     \$dom = \$response->dom;
//     \$_instance->logRenderedChild($cachedKey, \$response->id, \Livewire\Livewire::getRootElementTagName(\$dom));
// }
// echo \$dom;
// ? >
// EOT;
    }
}
