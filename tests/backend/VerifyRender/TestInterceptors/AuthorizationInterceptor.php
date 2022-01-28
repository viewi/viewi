<?php

namespace TestInterceptors;

use Viewi\Common\HttpClient;
use Viewi\Common\HttpHandler;

class AuthorizationInterceptor
{
    private HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
    }

    public function intercept(HttpHandler $handler)
    {
        // set request options before $handler->httpClient->setOptions(...)
        // call handle to continue with the request
        // echo "AuthorizationInterceptor call post\n";
        $this->http->post('/api/authorization/token/true')->then(function ($response) use ($handler) {            
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
            $handler->reject('Unauthorized');
        });
    }
}
