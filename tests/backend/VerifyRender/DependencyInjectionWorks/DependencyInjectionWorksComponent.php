<?php

use Viewi\BaseComponent;
class DependencyInjectionWorksComponent extends BaseComponent
{
    public NotificationDemoService $notificationService;
    public ?NotificationDemoService $ns = null;
    private HttpClientService $http;
    public array $test;
    public ?float $f;
    public string $name;
    public ?int $cost;
    function __init(
        NotificationDemoService $notificationService,
        HttpClientService $http,
        string $name,
        ?int $cost,
        ?NotificationDemoService $ns,
        ?float $f = 3,
        ?array $test = [5, 6]
    ) {
        $this->notificationService = $notificationService;
        $this->notificationService->Notify("My test app");
        $this->http = $http;
        $this->test = $test;
        $this->f = $f;
        $this->$ns = $ns;
        $this->name = $name;
        $this->cost = $cost;
    }
    public function NotifyNull(): string
    {
        return $this->ns ? 'ns is set' : 'ns is null';
    }
    public function HttpResult(): string
    {
        return json_encode($this->http->get('/', []));
    }
}
