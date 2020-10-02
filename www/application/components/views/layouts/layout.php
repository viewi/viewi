<?php

use Vo\BaseComponent;

class Layout extends BaseComponent
{
    public string $title = 'This is layout default title';
    public ObservableService $observableSubject;

    function __construct(ObservableService $observableSubject)
    {
        $this->observableSubject = $observableSubject;
    }
}
