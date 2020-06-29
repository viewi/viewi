<?php

use Vo\BaseComponent;

class HomePage extends BaseComponent
{
    public string $title = 'Wellcome to my awesome application\'s';
    public int $count = 0;
    public $messages;
    protected $any = 'Any\\\' var\\';
    private string $priv = 'Secret';
    
    function __construct()
    {
    }

    function Increment()
    {
        $this->count++;
        $this->priv .= "Code";
    }

    public function Test($argument): string
    {
        return 'Test ' . $argument;
    }
}

$test = 'Test';
