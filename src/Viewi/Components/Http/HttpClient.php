<?php

namespace Viewi\Components\Http;

use Viewi\App;
use Viewi\Builder\Attributes\CustomJs;
use Viewi\Builder\Attributes\Skip;
use Viewi\Components\Callbacks\Resolver;
use Viewi\DI\Singleton;

#[Singleton]
#[CustomJs]
class HttpClient
{
    public function __construct(private App $app)
    {
    }

    public function request(string $method, string $url, $body = null, ?array $headers = null): Resolver
    {
        $resolver = new Resolver(function () use ($url, $method) {
            return $this->app->run($url, $method);
        });
        return $resolver;
    }

    public function get(string $url, ?array $headers = null): Resolver
    {
        return $this->request('get', $url, null, $headers);
    }
}
