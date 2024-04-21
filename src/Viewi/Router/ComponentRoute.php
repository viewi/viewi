<?php

namespace Viewi\Router;

class ComponentRoute
{
    public ?string $lazyGroup = null;
    public function __construct(public string $component)
    {
    }
}
