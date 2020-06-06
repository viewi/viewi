<?php

use Vo\BaseComponent;

class CanRenderTagComponent extends BaseComponent
{
    public string $title = 'Wellcome to my awesome application';

    function getFullName(): string
    {
        return 'Jhon Doe';
    }

    function __construct()
    {
    }
}
