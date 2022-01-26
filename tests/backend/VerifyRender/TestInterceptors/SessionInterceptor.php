<?php

namespace TestInterceptors;

use Viewi\Common\HttpClient;
use Viewi\Common\HttpHandler;

class SessionInterceptor
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
        // echo "SessionInterceptor call post\n";
        $this->http->post('/api/authorization/session')->then(function ($response) use ($handler) {            
            // print_r(['SessionInterceptor response', $response]);
            $handler->httpClient->setOptions([
                'headers' => [
                    'X-SESSION-ID' => $response['session']
                ]
            ]);
            // echo "SessionInterceptor call handle\n";
            $handler->handle(function ($next) use ($handler) {
                // access or modify $handler->response after
                // call next if you are good with the response
                // otherwise it won't continue
                // echo "SessionInterceptor call next\n";
                $next();
            });
        }, function ($error) use ($handler) {
            // print_r(['SessionInterceptor error']);
            $handler->reject('Session has expired.');
        });
    }
}
