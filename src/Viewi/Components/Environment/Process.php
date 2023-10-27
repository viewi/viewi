<?php

namespace Viewi\Components\Environment;

use Viewi\App;
use Viewi\Builder\Attributes\CustomJs;
use Viewi\DI\Singleton;

// src\js\viewi\core\environment\process.ts

#[Singleton]
#[CustomJs]
class Process
{
    public bool $browser = false;
    public bool $server = true;

    public function __construct(private App $appInstance)
    {
    }

    public function getConfig()
    {
        return $this->appInstance->getPublicConfig();
    }

    // server-side only
    public function app()
    {
        return $this->appInstance;
    }
}
