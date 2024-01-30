<?php

namespace Viewi\Components;

use Viewi\Builder\Attributes\Skip;
use Viewi\Components\Context\ProvidesScope;
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
    public ProvidesScope $_provides;
    public ?BaseComponent $_parent = null;

    function emitEvent(string $eventName, $event = null)
    {
        // nothing here, only client-side
    }

    function provide(string $key, $value)
    {
        if ($this->_provides === $this->_parent?->_provides) {
            $this->_provides = new ProvidesScope($this->_provides);
        }
        $this->_provides->{$key} = $value;
    }

    function inject(string $key): mixed
    {
        return $this->_provides->{$key} ?? null;
    }
}
