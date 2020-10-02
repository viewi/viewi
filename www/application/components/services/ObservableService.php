<?php

class ObservableService
{
    public CountState $countState;

    function __construct(CountState $countState)
    {
        $this->countState = $countState;
    }
}
