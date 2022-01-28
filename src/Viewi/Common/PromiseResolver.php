<?php

namespace Viewi\Common;

use Exception;

class PromiseResolver
{
    const STATE_PENDING = 0;
    const STATE_SUCCESS = 1;
    const STATE_FAILURE = 2;

    public int $state = self::STATE_PENDING;
    private $lastError;
    private $result;
    private $action;
    private $always = null;

    function __construct(callable $action)
    {
        $this->action = $action;
    }

    public function always(callable $always)
    {
        $this->always = $always;
    }

    public function then(callable $success, callable $catch = null)
    {
        try {
            $this->result = ($this->action)(function ($result) use ($success) {
                $this->state = self::STATE_SUCCESS;
                $this->result = $result;
                $success($this->result);
                if ($this->always != null) {
                    ($this->always)();
                }
            }, function ($error) use ($catch) {
                $this->state = self::STATE_FAILURE;
                $this->lastError = $error;
                $catch($error);
                if ($this->always != null) {
                    ($this->always)();
                }
            });
        } catch (Exception $ex) {
            $this->lastError = $ex;
            $this->state = self::STATE_FAILURE;
            if ($catch !== null) {
                $catch($this->lastError);
                if ($this->always != null) {
                    ($this->always)();
                }
            } else {
                if ($this->always != null) {
                    ($this->always)();
                }
                throw $ex;
            }
        }
    }
}
