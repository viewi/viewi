<?php

namespace Viewi\Components\Http;

use Exception;
use Viewi\App;
use Viewi\Builder\Attributes\CustomJs;
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
        $resolver = new Resolver(function (callable $callback) use ($url, $method) {
            try {
                $callback($this->app->run($url, $method));
            } catch (Exception $ex) {
                $callback(null, $ex);
            }
        });
        return $resolver;
    }

    public function get(string $url, ?array $headers = null): Resolver
    {
        return $this->request('get', $url, null, $headers);
    }
}
