<?php

namespace Viewi\Components\Middleware;

interface IMIddleware
{
    function run(callable $next);
}
