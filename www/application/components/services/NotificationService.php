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

    private array $messages2 = [];

    public function __construct(HttpClientService $http)
    {
        $this->http = $http;
        $this->messages = [];
        $messages = [];
        $this->messages2 = [];
        $messages2 = [];
        $this->unknown = count($messages)
            + count($messages2)
            + count($this->messages)
            + count($this->messages2);
        $this->count = count([]);
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
$messages2 = [];