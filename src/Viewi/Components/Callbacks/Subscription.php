<?php

namespace Viewi\Components\Callbacks;

class Subscription
{
    /**
     * 
     * @param Subscriber $subscriber 
     * @param int $id
     * @param callable $notifyCallback 
     * @return void 
     */
    public function __construct(private Subscriber $subscriber, private int $id, public $notifyCallback)
    {
    }

    public function unsubscribe()
    {
        $this->subscriber->unsubscribe($this);
    }

    public function getId(): int
    {
        return $this->id;
    }
}
