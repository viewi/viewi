<?php

namespace Viewi\Components\Callbacks;

use Exception;

class Resolver
{
    /**
     * 
     * @var callable $onSuccess
     */
    protected $onSuccess;
    /**
     * 
     * @var callable $onError
     */
    protected $onError = null;
    /**
     * 
     * @var callable $onAlways
     */
    protected $onAlways;
    protected $result = null;
    protected $lastError = null;
    /**
     * 
     * @param callable $action 
     * @return void 
     */
    public function __construct(protected $action)
    {
    }

    public function error(callable $onError)
    {
        $this->onError = $onError;
    }

    public function success(callable $onSuccess)
    {
        $this->onSuccess = $onSuccess;
    }

    public function always(callable $always)
    {
        $this->onAlways = $always;
    }

    public function run() {
        $throwError = false;
        try {
            $this->result = ($this->action)();
            ($this->onSuccess)($this->result);
        } catch (Exception $ex) {
            $this->lastError = $ex;
            if ($this->onError !== null) {
                ($this->onError)($ex);
            } else {
                $throwError = true;
            }
        }
        if ($this->onAlways != null) {
            ($this->onAlways)();
        }
        if ($throwError) {
            throw $this->lastError;
        }
    }

    public function then(callable $onSuccess, callable $onError = null, callable $always = null)
    {
        $this->onSuccess = $onSuccess;
        if ($onError !== null) {
            $this->onError = $onError;
        }
        if ($always !== null) {
            $this->onAlways = $always;
        }
        $this->run();
    }
}
