<?php

namespace Viewi\Components\Middleware;

interface IMIddlewareContext
{
    function next(bool $allow = true);
}
