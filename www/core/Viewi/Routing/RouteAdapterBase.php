<?php

namespace Viewi\Routing;

abstract class RouteAdapterBase
{
    public abstract function register($method, $url, $components, $defaults);
}
