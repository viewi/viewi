<?php

use Vo\BaseComponent;

class HomePage extends BaseComponent
{
    public string $title = 'Wellcome to my awesome application';
    public int $count = 0;

    function __construct()
    {
    }

    function Increment()
    {
        $this->count++;
    }
}
