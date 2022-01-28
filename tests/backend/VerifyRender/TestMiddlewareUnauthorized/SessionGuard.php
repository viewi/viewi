<?php

namespace TestMiddleware;

use Viewi\Common\HttpClient;
use Viewi\Components\Interfaces\IMiddleware;

class SessionGuard implements IMiddleware
{
    private HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    public function run(callable $next)
    {
        // If we want to continue with the page (component) - we call $next(Continue = true): $next() or $next(true)
        $this->http->post('/api/authorization/session')->then(function ($response) use ($next) {
            $next();
        }, function ($error) use ($next) {
            // If we want to cancel - we call $next(false);
            $next(false);
        });
    }
}
