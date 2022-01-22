<?php

namespace Viewi\Components\Services;

use Viewi\Common\PromiseResolver;

class AsyncStateManager
{
    private array $queueMap = [];
    private array $pendingTypes = [];
    private array $callbacksMap = [];
    private int $stateIdIndex = 0;
    private int $currentStateId = 0;
    private array $events = [];
    /**
     * Initiate State
     * @return int State Id
     */
    public function initiateState(): int
    {
        $this->currentStateId = ++$this->stateIdIndex;
        // echo "State initiated: {$this->currentStateId}" . PHP_EOL;
        return $this->currentStateId;
    }

    /**
     * Restore State
     * @param int $id 
     * @return void 
     */
    public function setState(int $id): void
    {
        $this->currentStateId = $id;
        // echo "State restored: {$this->currentStateId}" . PHP_EOL;
    }

    /**
     * Get Current State
     * @return int State Id 
     */
    public function getState(): int
    {
        return $this->currentStateId;
    }

    public function track(PromiseResolver $promise, string $type = 'any'): PromiseResolver
    {
        $stateId = $this->currentStateId;
        if (!isset($this->queueMap[$stateId])) {
            $this->queueMap[$stateId] = 0;
        }
        $this->queueMap[$stateId]++;
        if (!isset($this->pendingTypes[$type])) {
            $this->pendingTypes[$type] = 0;
        }
        $this->pendingTypes[$type]++;
        // echo "Promise scheduled: {$type} {$stateId}" . PHP_EOL;
        $promise->always(function () use ($stateId, $type) {
            // echo "Promise resolved: {$stateId}" . PHP_EOL;
            // Restore state id
            $this->setState($stateId);
            $this->queueMap[$stateId]--;
            $this->pendingTypes[$type]--;
            if ($this->queueMap[$stateId] == 0) {
                unset($this->queueMap[$stateId]);
                if (isset($this->callbacksMap[$stateId])) {
                    ($this->callbacksMap[$stateId])();
                    unset($this->callbacksMap[$stateId]);
                }
            }
        });
        return $promise;
    }

    public function pending(): bool
    {
        return isset($this->queueMap[$this->currentStateId]);
    }

    public function all(callable $callback)
    {
        // call callback once everything is done
        $this->callbacksMap[$this->currentStateId] = $callback;
    }

    public function pendingByType(string $type): bool
    {
        return isset($this->pendingTypes[$type]) && $this->pendingTypes[$type] !== 0;
    }

    public function subscribe(string $eventName): PromiseResolver
    {
        return $this->track(new PromiseResolver(function ($resolve, $reject) use ($eventName) {
            if (!isset($this->events[$eventName])) {
                $this->events[$eventName] = [];
            }
            $this->events[$eventName][] = $resolve;
        }));
    }

    public function emit(string $eventName, $data = null)
    {
        if (isset($this->events[$eventName])) {
            foreach ($this->events[$eventName] as $resolve) {
                $resolve($data);
            }
        }
    }
}
