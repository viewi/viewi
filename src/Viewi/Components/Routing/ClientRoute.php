<?php

namespace Viewi\Components\Routing;

use Viewi\Components\Environment\Platform;
use Viewi\DI\Singleton;

#[Singleton]
class ClientRoute
{
    private array $config;
    public function __construct(private Platform $platform)
    {
        $this->config = $platform->getConfig();
    }

    public function navigateBack()
    {
        $this->platform->navigateBack();
    }

    public function navigate(string $url)
    {
        $this->platform->redirect($url);
    }

    public function getUrl(): ?string
    {
        return $this->platform->getCurrentUrl();
    }

    public function getUrlPath(): ?string
    {
        return $this->platform->getCurrentUrlPath();
    }

    public function getQueryParams()
    {
        return $this->platform->getQueryParams();
    }
}