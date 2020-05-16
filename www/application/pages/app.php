<?php

class AppComponent extends BaseComponent
{
    public string $about = 'This is php/js page engine';
    public string $model = 'Page';
    public string $url = '/';
    public array $testsArray = ['My test', 'your test'];
    public array $users = [];
    public Friend $friend;
    public string $dynamicTag = 'HomePage';
    public string $dynamicAttr = 'data-dynamic';
    public string $dynValue = 'Dynamic value';
    public string $content = 'Dynamic Content Test';
    public string $className = 'app-component';
    public bool $true = true;
    public bool $false = false;
    function __construct()
    {
        $this->friend = new Friend();
        $this->friend->Name = 'Jhon Doe';
        $this->friend->Age = 30;
        $letters = 'abcdefghijklmnopqrstuvwxyz';
        for ($i = 0; $i < 2; $i++) {
            $user = new Friend();
            $user->Name = 'Jhon' . $letters[26 - $i] . ' Doe' . $letters[$i];
            $user->Age = 30 + $i;
            $this->users['user-'.$i] = $user;
        }
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
