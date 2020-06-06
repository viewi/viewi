<?php

use HttpTools\HttpClientService;

class NotificationService
{
    private HttpClientService $http;

    /**
     * 
     * @var string[]
     */
    public array $messages = [];

    public function __construct(HttpClientService $http)
    {
        $this->http = $http;
    }

    public function Notify(string $message): void
    {
        $this->messages[] = $message;
    }

    public function Clear(): void
    {
        $this->messages = [];
    }
}
