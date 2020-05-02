<?php

class ComponentRenderer
{
    function __construct()
    {
        $this->childs = [];
    }
    /** @var BaseComponent */
    public $component;

    /** @var mixed */
    public $template;

    /** @var ComponentRenderer[]  */
    public $childs;
}
