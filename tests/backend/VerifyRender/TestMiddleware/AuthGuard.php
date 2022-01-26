<?php

namespace TestMiddleware;

use Viewi\Common\HttpClient;
use Viewi\Components\Interfaces\IMiddleware;

class AuthGuard implements IMiddleware
{
    private HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    public function run(callable $next)
    {
        // If we want to continue with the page (component) - we call $next(Continue = true): $next() or $next(true)
        $this->http->post('/api/authorization/token/1')->then(function ($response) use ($next) {
            $next();
        }, function () use ($next) {
            // If we want to cancel - we call $next(false);
            $this->router->navigate('/');
            $next(false);
        });
    }
}
