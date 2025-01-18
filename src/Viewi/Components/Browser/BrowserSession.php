<?php

namespace Viewi\Components\Browser;

use Viewi\DI\Singleton;

#[Singleton]
class BrowserSession
{
    public function getItem(string $key): ?string
    {
        <<<'javascript'
        return sessionStorage.getItem(key);
        javascript;
        return null;
    }

    public function setItem(string $key, string $value)
    {
        <<<'javascript'
        sessionStorage.setItem(key, value);
        javascript;
    }

    public function removeItem(string $key)
    {
        <<<'javascript'
        sessionStorage.removeItem(key);
        javascript;
    }

    public function clear()
    {
        <<<'javascript'
        sessionStorage.clear();
        javascript;
    }
}
