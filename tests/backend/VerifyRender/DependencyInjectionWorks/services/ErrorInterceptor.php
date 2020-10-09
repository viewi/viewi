<?php

use Viewi\BaseComponent;

class ErrorInterceptor
{
    public string $name = 'ErrorInterceptor';
    
    private HttpClientService $http;

    public function __construct(HttpClientService $http)
    {
        $this->http = $http;
    }
}
