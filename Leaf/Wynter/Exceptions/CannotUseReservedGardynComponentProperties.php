<?php

namespace Leaf\Wynter\Exceptions;

class CannotUseReservedWynterComponentProperties extends \Exception
{
    use BypassViewHandler;

    public function __construct($propertyName, $componentName)
    {
        parent::__construct(
            "Public property [{$propertyName}] on [{$componentName}] component is reserved for internal Wynter use."
        );
    }
}
