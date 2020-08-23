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
        $this->messages['test'] = new stdClass();
        $this->messages[0] = new stdClass();
        $this->messages[0]->Group[1]->Name = 'Test';
        $i = 1;
        $this->messages[$i] = new stdClass();
        $messages = [];
        $this->messages2 = [];
        $messages2 = [];
        $this->unknown = count($messages)
            + count($messages2)
            + count($this->messages)
            + count($this->messages2);
        $this->count = count($this->GetArray());
        echo count($this->GetArray());
    }

    public function GetArray()
    {
        return $this->messages;
    }

    public function GetCount(): int
    {
        return count($this->GetArray());
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
