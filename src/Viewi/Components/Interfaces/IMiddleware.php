<?php

namespace Viewi\Components\Interfaces;

interface IMiddleware
{
    function run(callable $next);
}
