<?php

namespace Viewi\Common;

class ClientRouter
{
    public function navigateBack()
    {
        // client side only
    }

    public function navigate(string $url)
    {
        // TODO: validate, make helper
        header('Location: ' . $url);
    }

    public function getUrl(): string
    {
        return isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
    }
}
