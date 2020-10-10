<?php

namespace Viewi;

abstract class BaseComponent
{
    function __invoke(...$arguments)
    {
        return App::run(get_class($this));
    }
}
