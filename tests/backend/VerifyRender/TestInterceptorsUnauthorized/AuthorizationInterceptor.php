<?php

namespace TestInterceptors;

use Viewi\Common\ClientRouter;
use Viewi\Common\HttpClient;
use Viewi\Common\HttpHandler;

class AuthorizationInterceptor
{
    private HttpClient $http;
    private ClientRouter $router;

    public function __construct(HttpClient $http, ClientRouter $router)
    {
        $this->http = $http;
        $this->router = $router;
    }

    public function intercept(HttpHandler $handler)
    {
        // set request options before $handler->httpClient->setOptions(...)
        // call handle to continue with the request
        // echo "AuthorizationInterceptor call post\n";
        $this->http->post('/api/authorization/token/0')->then(function ($response) use ($handler) {            
            // print_r(['AuthorizationInterceptor response', $response]);
            $handler->httpClient->setOptions([
                'headers' => [
                    'Authorization' => $response['token']
                ]
            ]);
            // echo "AuthorizationInterceptor call handle\n";
            $handler->handle(function ($next) use ($handler) {
                // access or modify $handler->response after
                // call next if you are good with the response
                // otherwise it won't continue
                // echo "AuthorizationInterceptor call next\n";
                $next();
            });
        }, function ($error) use ($handler) {
            // print_r(['AuthorizationInterceptor error']);
            $this->router->navigate('/');
            $handler->reject('Unauthorized');
        });
    }
}
