<?php

namespace Viewi\Routing;

class RouteItem
{
    public string $url;
    public string $method;
    /**
     * 
     * @var string|callable
     */
    public $action;
    /**
     * 
     * @var string|callable
     */
    public $component;
    public ?array $defaults = null;
    public array $wheres;

    function __construct(string $method, string $url, $action, $component, ?array $defaults = null)
    {
        $this->method = $method;
        $this->url = $url;
        $this->action = $action;
        $this->component = $component;
        $this->defaults = $defaults;
        $this->wheres = [];
    }

    public function where($wheresOrName, $expr)
    {
        if ($wheresOrName !== null && is_array($wheresOrName)) {
            $this->wheres = array_merge($this->wheres, $wheresOrName);
        } else if ($expr) {
            $this->wheres[$wheresOrName] = $expr;
        }
        return $this;
    }
}
