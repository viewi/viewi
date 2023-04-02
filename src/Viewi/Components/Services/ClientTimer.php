<?php

namespace Viewi\Components\Services;

class ClientTimer
{
    public static function setTimeoutStatic(callable $action, int $milliseconds): int
    {
        <<<'javascript'
        return window.setTimeout(action, milliseconds);
        javascript;
        // nothing on server-side
        return 0;
    }

    public function setTimeout(callable $action, int $milliseconds): int
    {
        return self::setTimeoutStatic($action, $milliseconds);
    }

    public static function clearTimeoutStatic(int $timerId)
    {
        <<<'javascript'
        window.clearTimeout(timerId);
        return;
        javascript;
        // nothing on server-side 
    }

    public function clearTimeout(int $timerId)
    {
        self::clearTimeoutStatic($timerId);
    }

    public static function setIntervalStatic(callable $action, int $milliseconds): int
    {
        <<<'javascript'
        return window.setInterval(action, milliseconds);
        javascript;
        // nothing on server-side
        return 0;
    }

    public function setInterval(callable $action, int $milliseconds): int
    {
        return self::setIntervalStatic($action, $milliseconds);
    }

    public static function clearIntervalStatic(int $timerId)
    {
        <<<'javascript'
        window.clearInterval(timerId);
        return;
        javascript;
        // nothing on server-side
    }

    public function clearInterval(int $timerId)
    {
        self::clearIntervalStatic($timerId);
    }
}
