<?php

use Viewi\BaseComponent;

class Layout extends BaseComponent
{
    public string $title = 'This is layout default title';
    public ObservableService $observableSubject;
    public $dynamicName = 'UserItem';
    
    function __construct(ObservableService $observableSubject)
    {
        $this->observableSubject = $observableSubject;
    }
}
