<?php

namespace Leaf\Wynter\Exceptions;

class CorruptComponentPayloadException extends \Exception
{
    use BypassViewHandler;

    public function __construct($component)
    {
        parent::__construct(
            "Wynter encountered corrupt data when trying to hydrate the [{$component}] component. \n".
            "Ensure that the [name, id, data] of the Wynter component wasn't tampered with between requests."
        );
    }
}
