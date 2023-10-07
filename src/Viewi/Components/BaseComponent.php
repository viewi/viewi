<?php

namespace Viewi\Components;

use Viewi\Builder\Attributes\Skip;
use Viewi\Components\DOM\HtmlNode;

#[Skip]
abstract class BaseComponent
{
    public string $__id = '';
    public array $_props = [];
    public ?HtmlNode $_element = null;
    /**
     * 
     * @var HtmlNode[]
     */
    public array $_refs = [];
    // public ?HtmlNode $_element = null;
    /**
     * 
     * @var array<string|int,bool>
     */
    public array $_slots = [];

    function emitEvent(string $eventName, $event = null)
    {
        // nothing here, only client-side
    }
}
