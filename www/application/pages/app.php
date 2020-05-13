<?php

class AppComponent extends BaseComponent
{
    public string $about = 'This is php/js page engine';
    public string $model = 'Page';
    public string $url = '/';
    public array $testsList = ['My test', 'your test'];
    public Friend $friend;
    public string $dynamicTag = 'HomePage';
    public string $dynamicAttr = 'data-dynamic';
    public string $dynValue = 'Dynamic value';
    public string $content = 'Dynamic Content Test';
    function __construct()
    {
        $this->friend = new Friend();
        $this->friend->Name = 'Jhon Doe';
        $this->friend->Age = 30;
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
