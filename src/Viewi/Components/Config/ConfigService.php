<?php

namespace Viewi\Components\Config;

use Viewi\Components\Environment\Platform;
use Viewi\DI\Singleton;

#[Singleton]
class ConfigService
{
    private array $config;
    public function __construct(private Platform $platform)
    {
        $this->config = $platform->getConfig();
    }

    public function getAll(): array
    {
        return $this->config;
    }

    public function get(string $name)
    {
        return $this->config[$name] ?? null;
    }
}
