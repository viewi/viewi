<?php

namespace Viewi\Components\Routing;

use Viewi\Components\Callbacks\Subscriber;
use Viewi\Components\Environment\Platform;
use Viewi\DI\Singleton;

#[Singleton]
class ClientRoute
{
    private Subscriber $urlUpdateSubscriber;
    public function __construct(private Platform $platform)
    {
        $this->urlUpdateSubscriber = new Subscriber($this->platform->getCurrentUrlPath());
        $this->platform->onUrlUpdate(function () {
            $this->urlUpdateSubscriber->publish($this->platform->getCurrentUrlPath());
        });
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

    public function urlWatcher(): Subscriber
    {
        return $this->urlUpdateSubscriber;
    }

    public function setResponseStatus(int $status): void
    {
        $this->platform->setResponseStatus($status);
    }
}
