<?php

class AppComponent extends BaseComponent
{
    public string $about = 'This is php/js page engine';

    public string $model = 'Page';

    function __construct()
    {
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
