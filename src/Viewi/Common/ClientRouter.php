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
}
