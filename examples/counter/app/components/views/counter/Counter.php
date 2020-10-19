<?php

namespace App\Components\Views\Counter;

use Viewi\BaseComponent;

class Counter extends BaseComponent
{
    public int $count = 0;

    public function increment(): void
    {
        $this->count++;
    }
}
