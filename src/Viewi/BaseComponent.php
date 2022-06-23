<?php

namespace Viewi;

use Viewi\DI\IContainer;
use Viewi\DOM\Elements\HtmlNode;

abstract class BaseComponent
{
    public array $_props = [];
    public ?HtmlNode $_element = null;

    function __invoke($params, ?IContainer $container = null)
    {
        return App::run(get_class($this), $params, $container);
    }

    function emitEvent(string $eventName, $event = null)
    {
        // nothing here, only client-side
    }
}
