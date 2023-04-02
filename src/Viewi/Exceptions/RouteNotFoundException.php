<?php

namespace Viewi\Exceptions;

use InvalidArgumentException;
use Throwable;

class RouteNotFoundException extends InvalidArgumentException
{
    protected array $routeData;


    public function __construct($message = "", $code = 0, Throwable $previous = null, array $routeData = [])
    {
        parent::__construct($message, $code, $previous);
        $this->routeData = $routeData;
    }

    /**
     * Missing route data
     *
     * @return array
     */
    public function getRouteData(): array
    {
        return $this->routeData;
    }
}