<?php

use Vo\BaseComponent;

class HomePage extends BaseComponent
{
    public string $title = 'Wellcome to my awesome application';
    public int $count = 0;
    protected $any = 'Any var';
    private string $priv = 'Secret';
    function __construct()
    {
    }

    function Increment()
    {
        $this->count++;
    }
}

$test = 'Test';
