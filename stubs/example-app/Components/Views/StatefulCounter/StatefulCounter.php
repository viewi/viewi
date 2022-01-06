<?php

namespace Components\Views\StatefulCounter;

use Components\Services\Reducers\CounterReducer;
use Viewi\BaseComponent;

class StatefulCounter extends BaseComponent
{
    public CounterReducer $counter;

    public function __init(CounterReducer $reducer)
    {
        $this->counter = $reducer;
    }
}
