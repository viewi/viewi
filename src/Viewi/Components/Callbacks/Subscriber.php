<?php

namespace Viewi\Components\Callbacks;

class Subscriber
{
    /**
     * 
     * @var { data: mixed } | NULL
     */
    private $dataState = null;
    /**
     * 
     * @var Subscription[]
     */
    private array $subscribers = [];

    public function __construct($defaultValue = null)
    {
        if ($defaultValue !== null) {
            $this->dataState = ['data' => $defaultValue];
        }
    }

    public function subscribe(callable $callback): Subscription
    {
        $subscription = new Subscription($this, $callback);
        $this->subscribers[] = $subscription;
        ($subscription->notifyCallback)($this->dataState ? $this->dataState['data'] : null);
        return $subscription;
    }

    public function unsubscribe(Subscription $subscription)
    {
        $index = array_search($subscription, $this->subscribers);
        if ($index !== false) {
            array_splice($this->subscribers, $index, 1);
        }
    }

    public function notify()
    {
        foreach ($this->subscribers as $subscription) {
            ($subscription->notifyCallback)($this->dataState ? $this->dataState['data'] : null);
        }
    }

    public function reset()
    {
        $this->dataState = null;
        $this->notify();
    }

    public function publish($data)
    {
        $this->dataState = ['data' => $data];
        $this->notify();
    }
}
