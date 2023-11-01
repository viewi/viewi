<?php

namespace Viewi\Components\Middleware;

class MIddlewareContext implements IMIddlewareContext
{
    /**
     * 
     * @param mixed $callback (bool $allow = true) => void
     * @return void 
     */
    public function __construct(private $callback)
    {
    }

    function next(bool $allow = true)
    {
        ($this->callback)($allow);
    }
}
