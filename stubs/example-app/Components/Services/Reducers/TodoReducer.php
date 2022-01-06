<?php

namespace Components\Services\Reducers;

class TodoReducer
{
    public array $items = [];

    public function addNewItem(string $text)
    {
        $this->items[] = $text;
    }
}
