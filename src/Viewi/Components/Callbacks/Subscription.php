<?php

namespace Viewi\Components\Callbacks;

class Subscription
{
    /**
     * 
     * @param Subscriber $subscriber 
     * @param callable $notifyCallback 
     * @return void 
     */
    public function __construct(private Subscriber $subscriber, public $notifyCallback)
    {
    }

    public function unsubscribe()
    {
        $this->subscriber->unsubscribe($this);
    }
}
