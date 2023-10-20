<?php

namespace Viewi\Components\Http;

use Viewi\Builder\Attributes\CustomJs;
use Viewi\Builder\Attributes\Skip;
use Viewi\Components\Callbacks\Resolver;
use Viewi\DI\Singleton;

#[Singleton]
#[CustomJs]
class HttpClient
{
    public function request(string $method, string $url, $body = null, ?array $headers = null): Resolver
    {
        $resolver = new Resolver(function () {
            return null;
        });
        return $resolver;
    }

    public function get(string $url, ?array $headers = null): Resolver
    {
        return $this->request('get', $url, null, $headers);
    }
}
