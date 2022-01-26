<?php

use Viewi\BaseComponent;

class CanRenderTagComponent extends BaseComponent
{
    public string $title = 'Welcome to my awesome application';

    function getFullName(): string
    {
        return 'Jhon Doe';
    }

    function __init()
    {
    }
}
