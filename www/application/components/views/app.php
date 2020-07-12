<?php

namespace Silly\MyApp;

$h = 7;

use Vo\BaseComponent;
use \NotificationService;
use HttpTools\HttpClientService;

$k = 5;

class AppComponent extends BaseComponent
{
    public string $about = 'This is php/js page engine';
    public string $model = "Page";
    public string $url = '';
    public string $url2 = '/';
    public string $url3 = 'U';
    public array $testsArray = ['My test', 'your test'];
    /** 
     * @var Friend[] 
     * */
    public array $users = [];
    public array $booleans = [true, false];
    public Friend $friend;
    private array $MultiTest = array(
        "fruits"  => array("a" => "orange", "b" => "banana", "c" => "apple"),
        "numbers" => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10),
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
        ?NotificationService $notificationService2,
        ?NotificationService $notificationService3,
        ?NotificationService $notificationService4,
        ?float $f = 30,
        ?array $test = [5, 6],
        ?array $test2 = [5, 6],
        ?array $test3 = [5, 6],
        ?array $test4 = [5, 6]
    ) {
        $this->notificationService = $notificationService;
        $this->http = $http;
        $this->friend = new Friend();
        $this->friend2 = new Friend();
        $this->friend->Name = "Jhon Doe {$this->model}";
        $this->friend->Age = $f;
        $letters = 'abcdefghijklmnopqrstuvwxyz';
        for ($i = 0; $i < 3; $i++) {
            $user = new Friend(); // new user
            $user->Name = 'Jhon' . $letters[26 - $i] . ' Doe' . $letters[$i];
            $user->Age = 30 + $i;
            $this->users["$i"] = $user;
            $this->users["{$this->friend2->Name}"] = $user;
            $this->users["ID-$i"] = $user;
            $this->users["ID-$test[1]test"] = $user;
            $this->users["ID-{$this->friend2->Name}test"] = $user;
        }
        foreach ($this->users as $user) {
            $user->Name;
        }
        foreach ($this->users as $id => $user) {
            $id . $user->Name;
        }
        count($this->MultiTest);
        $letters = 'X';
        $test = [5, $test];
        $f = 98;
        $name = 'My name';
        $http = new HttpClientService();
    }

    function getFullName(): string
    {
        return 'Jhon Doe';
    }

    function getOccupation(): string
    {
        $letters = 'X';
        return 'Web developer';
    }
}
$letters = 'X';
class Friend
{
    public string $Name;
    public int $Age;
}
