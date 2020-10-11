<?php

namespace Viewi\Common;

use Exception;

class PromiseResolver
{
    const STATE_PENDING = 0;
    const STATE_SUCCESS = 1;
    const STATE_FAILURE = 2;

    public int $state = self::STATE_PENDING;
    private Exception $lastException;
    private $result;
    private $action;

    function __construct(callable $action)
    {
        $this->action = $action;
    }

    public function then(callable $success, callable $error = null)
    {
        try {
            $this->result = ($this->action)();
            $this->state = self::STATE_SUCCESS;
            $success($this->result);
        } catch (Exception $ex) {
            $this->lastException = $ex;
            $this->state = self::STATE_FAILURE;
            if ($error !== null) {
                $error($this->lastException);
            } else {
                throw $ex;
            }
        }
    }
}
