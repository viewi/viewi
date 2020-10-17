<?php

namespace App\Components\Views;

use Viewi\BaseComponent;

class GetStartedComponent extends BaseComponent
{

    public string $title = 'Welcome to my awesome application!';

    public $rawHtml = '<b>WOW, this is so exciting!</b>';

    public function greet(string $name): string
    {
        return "Hello, $name, it's so nice to meet you!";
    }
}
