<?php

namespace Viewi\Components\Services;

class ClientTimer
{
    public function setTimeout(callable $action, int $milliseconds): int
    {
        <<<'javascript'
        return window.setTimeout(action, milliseconds);
        javascript;
        // nothing on server-side
        return 0;
    }

    public function clearTimeout(int $timerId)
    {
        <<<'javascript'
        window.clearTimeout(timerId);
        return;
        javascript;
        // nothing on server-side 
    }

    public function setInterval(callable $action, int $milliseconds): int
    {
        <<<'javascript'
        return window.setInterval(action, milliseconds);
        javascript;
        // nothing on server-side
        return 0;
    }

    public function clearInterval(int $timerId)
    {
        <<<'javascript'
        window.clearInterval(timerId);
        return;
        javascript;
        // nothing on server-side
    }
}
