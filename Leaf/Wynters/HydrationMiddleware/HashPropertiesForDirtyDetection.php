<?php

namespace Leaf\Wynter\HydrationMiddleware;

class HashPropertiesForDirtyDetection implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        $unHydratedInstance->hashPropertiesForDirtyDetection();
    }

    public static function dehydrate($instance, $response)
    {
        $response->dirtyInputs = $instance->getDirtyProperties();
    }
}
