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
    private array $scopeResponses = [];

    public function __construct(private App $app)
    {
    }

    public function getScopeResponses()
    {
        return $this->scopeResponses;
    }

    public function request(string $method, string $url, $body = null, ?array $headers = null): Resolver
    {
        $dataKey = json_encode($body);
        $requestKey = "{$method}_{$url}_$dataKey";
        $resolver = new Resolver(function (callable $callback) use ($url, $method, $requestKey) {
            try {
                $response = $this->app->run($url, $method);
                $this->scopeResponses[$requestKey] = $response;
                $callback($response);
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
