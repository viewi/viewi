<?php

namespace Viewi\Common;

use Viewi\WebComponents\IHttpContext;

class ClientRouter
{
    private IHttpContext $httpContext;

    public function __construct(IHttpContext $httpContext)
    {
        $this->httpContext = $httpContext;
    }

    public function navigateBack()
    {
        // client side only
    }

    public function navigate(string $url)
    {
        $this->httpContext->setResponseHeader('Location', $url);
    }

    public function getUrl(): string
    {
        return $this->httpContext->getCurrentUrl();
    }
}
