<?php

namespace Viewi\Components\Environment;

use Viewi\App;
use Viewi\Builder\Attributes\CustomJs;
use Viewi\DI\Singleton;
use Viewi\Engine;

// src\js\viewi\core\environment\process.ts

#[Singleton]
#[CustomJs]
class Platform
{
    public bool $browser = false;
    public bool $server = true;
    public array $httpState = [];

    public function __construct(private App $appInstance, private Engine $engine)
    {
    }

    public function getConfig()
    {
        return $this->appInstance->getPublicConfig();
    }

    public function redirect(string $url)
    {
        $response = $this->engine->getResponse();
        $response->headers['Location'] = $url;
    }

    public function navigateBack()
    {
        // only client-side
    }

    public function getCurrentUrl(): ?string
    {
        return $this->engine->getRequest()?->url;
    }

    // server-side only
    public function app()
    {
        return $this->appInstance;
    }

    // server-side only
    public function engine()
    {
        return $this->engine;
    }
}
