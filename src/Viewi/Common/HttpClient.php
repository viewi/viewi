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

    public function request($type, $url, $data)
    {
        $resolver = new PromiseResolver(function () use ($type, $url, $data) {
            return Route::handle($type, $url, $data);
        });

        return $resolver;
    }

    public function post($url, $data)
    {
        return $this->request('post', $url, $data);
    }

    public function put($url, $data)
    {
        return $this->request('put', $url, $data);
    }

    public function delete($url, $data)
    {
        return $this->request('delete', $url, $data);
    }
}
