<?php

use Vo\BaseComponent;
class ComplexTestComponent extends BaseComponent
{
    public string $about = 'This is php/js page engine';
    public string $model = 'Page';
    public string $url = '/';
    public array $testsArray = ['My test', 'your test'];
    public array $users = [];
    public string $className = 'app-component';
    public bool $true = true;
    public bool $false = false;
    public string $html = '<b>raw html demo</b>';

    function __construct()
    {
        $letters = 'abcdefghijklmnopqrstuvwxyz';
        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->Name = 'Jhon' . $letters[26 - $i] . ' Doe' . $letters[$i];
            $user->Age = 30 + $i;
            $this->users['ID-' . $i] = $user;
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

class User
{
    public string $Name;
    public int $Age;
}
