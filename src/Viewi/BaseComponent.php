<?php

namespace Viewi;

use Viewi\DI\IContainer;
use Viewi\DOM\Elements\HtmlNode;

abstract class BaseComponent
{
    public string $__id;
    public array $_props = [];
    /**
     * 
     * @var HtmlNode[]
     */
    public array $_refs = [];
    public ?HtmlNode $_element = null;
    /**
     * 
     * @var array<string|int,bool>
     */
    public array $_slots = [];

    function __invoke($params, ?IContainer $container = null)
    {
        return App::run(get_class($this), $params, $container);
    }

    function emitEvent(string $eventName, $event = null)
    {
        // nothing here, only client-side
    }
}
