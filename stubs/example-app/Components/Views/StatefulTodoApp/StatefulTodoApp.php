<?php

namespace Components\Views\StatefulTodoApp;

use Components\Services\Reducers\TodoReducer;
use Viewi\BaseComponent;
use Viewi\DOM\Events\DOMEvent;

class StatefulTodoApp extends BaseComponent
{
    public string $text = '';
    public TodoReducer $todo;

    public function __init(TodoReducer $reducer)
    {
        $this->todo = $reducer;
    }

    public function handleSubmit(DOMEvent $event)
    {
        $event->preventDefault();
        if (strlen($this->text) == 0) {
            return;
        }
        $this->todo->addNewItem($this->text);
        $this->text = '';
    }
}
