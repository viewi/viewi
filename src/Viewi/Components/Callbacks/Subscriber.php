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
    private array $subscribers = /* @jsobject */ [];
    private int $idGenerator = 0;

    public function __construct($defaultValue = null)
    {
        if ($defaultValue !== null) {
            $this->dataState = ['data' => $defaultValue];
        }
    }

    public function subscribe(callable $callback): Subscription
    {
        $subscriptionId = ++$this->idGenerator;
        $subscription = new Subscription($this, $subscriptionId, $callback);
        $this->subscribers[$subscriptionId] = $subscription;
        ($subscription->notifyCallback)($this->dataState ? $this->dataState['data'] : null);
        return $subscription;
    }

    public function unsubscribe(Subscription $subscription)
    {
        $subscriptionId = $subscription->getId();
        if (isset($this->subscribers[$subscriptionId])) {
            unset($this->subscribers[$subscriptionId]);
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
