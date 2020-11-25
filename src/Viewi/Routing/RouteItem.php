<?php

namespace Viewi\Routing;

class RouteItem
{
    function __construct(string $method, string $url, string $action, string $component, ?array $defaults = null)
    {
        $this->method = $method;
        $this->url = $url;
        $this->action = $action;
        $this->component = $component;
        $this->defaults = $defaults;
    }

    public string $url;
    public string $method;
    public string $action;
    public string $component;
    public ?array $defaults = null;
}
