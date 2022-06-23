<?php

namespace Viewi\DOM\Events;

use Viewi\DOM\Elements\FileInputElement;

abstract class DOMEvent
{
    public abstract function type();
    public FileInputElement $target;
    public $detail;
    public abstract function currentTarget();
    public abstract function eventPhase();
    public abstract function bubbles();
    public abstract function cancelable();
    public abstract function defaultPrevented();
    public abstract function composed();
    public abstract function timeStamp();
    public abstract function srcElement();
    public abstract function returnValue();
    public abstract function cancelBubble();
    public abstract function path();
    public $NONE;
    public $CAPTURING_PHASE;
    public $AT_TARGET;
    public $BUBBLING_PHASE;
    public abstract function composedPath();
    public abstract function initEvent();
    public abstract function preventDefault();
    public abstract function stopImmediatePropagation();
    public abstract function stopPropagation();
}
