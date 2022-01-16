<?php

namespace Viewi\WebComponents;

class HttpContext implements IHttpContext
{

    public function getResponseHeaders(): ?array
    {
        return [];
    }

    public function setResponseHeader(string $key, string $value): void
    {
        header("$key: $value");
    }

    public function getCurrentUrl(): ?string
    {
        return isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
    }
}
