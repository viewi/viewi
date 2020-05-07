<?php

class ComponentRenderer
{
    function __construct()
    {
        $this->childs = [];
    }

    public BaseComponent $component;

    /** @var mixed */
    public $template;

    /** @var ComponentRenderer[]  */
    public array $childs;
}
