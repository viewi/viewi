<?php

use Viewi\BaseComponent;

class Layout extends BaseComponent
{
    public string $title = 'This is layout default title';
    public ObservableService $observableSubject;
    public $dynamicName = 'UserItem';
    
    function __init(ObservableService $observableSubject)
    {
        $this->observableSubject = $observableSubject;
    }
}
