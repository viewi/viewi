<?php

namespace Viewi;

use Viewi\DI\IContainer;

abstract class BaseComponent
{
    public array $_props = [];

    function __invoke($params, ?IContainer $container = null)
    {
        return App::run(get_class($this), $params, $container);
    }

    function emitEvent(string $eventName, $event = null)
    {
        // nothing here, only client-side
    }
}
