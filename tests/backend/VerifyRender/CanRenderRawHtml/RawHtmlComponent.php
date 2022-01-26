<?php

use Viewi\BaseComponent;

class RawHtmlComponent extends BaseComponent
{
    public string $title = 'Welcome to my awesome application';
    public string $html = '<b>raw html demo</b>';
    function __init()
    {
    }
}
