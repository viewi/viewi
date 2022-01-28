<?php

use Viewi\BaseComponent;

class CanRenderTagComponent extends BaseComponent
{
    public string $title = 'Welcome to my awesome application';

    function getFullName(): string
    {
        return 'John Doe';
    }

    function __init()
    {
    }
}
