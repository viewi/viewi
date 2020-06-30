<?php

namespace Silly\MyApp;

use Vo\BaseComponent;
use \NotificationService;
use HttpTools\HttpClientService;

class AppComponent extends BaseComponent
{
    public string $about = 'This is php/js page engine';
    public string $model = "Page";
    public string $url = '';
    public string $url2 = '/';
    public string $url3 = 'U';
    public array $testsArray = ['My test', 'your test'];
    public array $users = [];
    public array $booleans = [true, false];
    public Friend $friend;
    private array $MultiTest = array(
        "fruits"  => array("a" => "orange", "b" => "banana", "c" => "apple"),
        "numbers" => array(1, 2, 3, 4, 5, 6),
        [true, false],
        "holes"   => array("first", 5 => "second", "third"),
        array(1, 1, 1, 1,  1, 8 => 1,  4 => 1, 19, 3 => 13),
    );
    private Friend $friend2;
    public string $dynamicTag = 'HomePage';
    public string $dynamicAttr = 'data-dynamic';
    public string $dynValue = 'Dynamic value';
    public string $content = 'Dynamic Content Test';
    public string $className = 'app-component';
    public bool $true = true;
    public bool $false = false;
    public string $html = "<b>raw html - demo</b>";
    public NotificationService $notificationService;
    private HttpClientService $http;
    function __construct(
        NotificationService $notificationService,
        HttpClientService $http,
        string $name,
        ?int $cost,
        ?NotificationService $ns,
        ?float $f = 3,
        ?array $test = [5, 6]
    ) {
        $this->notificationService = $notificationService;
        $this->http = $http;
        $this->friend = new Friend();
        $this->friend2 = new Friend();
        $this->friend->Name = "Jhon Doe {$this->model}";
        $this->friend->Age = 30;
        $letters = 'abcdefghijklmnopqrstuvwxyz';
        for ($i = 0; $i < 3; $i++) {
            $user = new Friend();
            $user->Name = 'Jhon' . $letters[26 - $i] . ' Doe' . $letters[$i];
            $user->Age = 30 + $i;
            $this->users["ID-$i"] = $user;
        }
        count($this->MultiTest);
    }

    function getFullName(): string
    {
        return 'Jhon Doe';
    }

    function getOccupation(): string
    {
        return 'Web developer';
    }
}

class Friend
{
    public string $Name;
    public int $Age;
}
