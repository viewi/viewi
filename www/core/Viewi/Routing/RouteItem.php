<?php

namespace Viewi\Routing;

class RouteItem
{
    function __construct(string $method, string $url, string $component, ?array $defaults = null)
    {
        $this->method = $method;
        $this->url = $url;
        $this->component = $component;
        $this->defaults = $defaults;
    }
    public string $url;
    public string $method;
    public string $component;
    public ?array $defaults = null;
}
