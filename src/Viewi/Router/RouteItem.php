<?php

namespace Viewi\Router;

class RouteItem
{
    public string $url;
    public string $method;
    /**
     * 
     * @var string|callable
     */
    public $action;
    public ?array $defaults = null;
    public array $wheres;
    /**
     * 
     * @var callable
     */
    public $transformCallback = null;

    function __construct(string $method, string $url, $action, ?array $defaults = null, array $wheres = [])
    {
        $this->method = $method;
        $this->url = $url;
        $this->action = $action;
        $this->defaults = $defaults;
        $this->wheres = $wheres;
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

    public function transform($transform)
    {
        $this->transformCallback = $transform;
    }
}
