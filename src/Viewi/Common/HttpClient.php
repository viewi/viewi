<?php

namespace Viewi\Common;

use Viewi\Routing\Route;

class HttpClient
{
    public function request($type, $url, $data = null, ?array $options = null)
    {
        $resolver = new PromiseResolver(function () use ($type, $url, $data) {
            return Route::handle($type, $url, $data);
        });

        return $resolver;
    }

    public function get($url, ?array $options = null)
    {
        return $this->request('get', $url, null, $options);
    }

    public function post($url, $data = null, ?array $options = null)
    {
        return $this->request('post', $url, $data, $options);
    }

    public function put($url, $data = null, ?array $options = null)
    {
        return $this->request('put', $url, $data, $options);
    }

    public function delete($url, $data = null, ?array $options = null)
    {
        return $this->request('delete', $url, $data, $options);
    }
}
