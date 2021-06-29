<?php

namespace Viewi\Routing;

abstract class RouteAdapterBase
{
    public abstract function register($method, $url, $component, $defaults);

    public abstract function handle($method, $url, $params = null);
}
