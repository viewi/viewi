<?php

namespace Viewi\Common;

use Viewi\Routing\Route;

class HttpClient
{
    public function get($url)
    {
        $resolver = new PromiseResolver(function () use ($url) {
            return Route::handle('get', $url);
        });

        return $resolver;
    }

    public function post($url, $data)
    {
        $resolver = new PromiseResolver(function () use ($url, $data) {
            return Route::handle('post', $url, $data);
        });

        return $resolver;
    }
}
