<?php

namespace Components\Views\TodoApp;

use Viewi\BaseComponent;
use Viewi\DOM\Events\DOMEvent;

class TodoApp extends BaseComponent
{
    public string $text = '';
    public array $items = [];

    public function handleSubmit(DOMEvent $event)
    {
        $event->preventDefault();
        if (strlen($this->text) == 0) {
            return;
        }
        $this->items[] = $this->text;
        $this->text = '';
    }
}
