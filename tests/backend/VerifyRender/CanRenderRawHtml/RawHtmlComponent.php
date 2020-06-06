<?php

use Vo\BaseComponent;

class RawHtmlComponent extends BaseComponent
{
    public string $title = 'Wellcome to my awesome application';
    public string $html = '<b>raw html demo</b>';
    function __construct()
    {
    }
}
