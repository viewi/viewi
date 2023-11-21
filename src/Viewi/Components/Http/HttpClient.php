<?php

namespace Viewi\Components\Http;

use Exception;
use Viewi\Builder\Attributes\CustomJs;
use Viewi\Components\Callbacks\Resolver;
use Viewi\Components\Environment\Platform;
use Viewi\Components\Http\Interceptor\RequestHandler;
use Viewi\Components\Http\Interceptor\ResponseHandler;
use Viewi\Components\Http\Message\Request;
use Viewi\Components\Http\Message\Response;
use Viewi\DI\Singleton;

#[Singleton]
#[CustomJs]
class HttpClient
{
    /**
     * 
     * @var array
     */
    private array $interceptors = [];

    public function __construct(private Platform $platform)
    {
    }

    public function getScopeResponses()
    {
        return $this->platform->httpState;
    }

    public function request(string $method, string $url, $body = null, ?array $headers = null): Resolver
    {
        $request = new Request($url, $method, $headers ?? [], $body);
        $resolver = new Resolver(function (callable $callback) use ($request) {
            try {
                $onHandle = function (Request $request, array $interceptorInstances, bool $continue) use ($callback) {
                    if ($continue) {
                        $dataKey = json_encode($request->body);
                        $requestKey = "{$request->method}_{$request->url}_$dataKey";
                        // Helpers::debug(['calling', $request]);
                        $data = $this->platform->app()->run($request->url, $request->method);
                        $this->platform->httpState[$requestKey] = json_encode($data);
                        // continue to response handler
                        $response = new Response('/', 200, 'OK', [], $data);
                        $this->interceptResponse($response, $callback, $interceptorInstances);
                    } else {
                        $response = new Response('/', 0, 'Rejected', [], null);
                        $this->interceptResponse($response, $callback, $interceptorInstances);
                    }
                };

                $requestHandler = new RequestHandler($onHandle, $this->platform->engine(), $this->interceptors, $request);
                $requestHandler->next($request);
            } catch (Exception $ex) {
                $callback(null, $ex);
            }
        });
        return $resolver;
    }

    private function interceptResponse(Response $response, $callback, array $interceptorInstances)
    {
        $onHandle = function (Response $response, bool $continue) use ($callback) {
            if ($continue && $response->status >= 200 && $response->status < 300) {
                $callback($response->body);
            } else {
                $callback(null, $response->body ?? 'Failed');
            }
        };
        $responseHandler = new ResponseHandler($onHandle, $interceptorInstances);
        $responseHandler->next($response);
    }

    public function get(string $url, ?array $headers = null): Resolver
    {
        return $this->request('get', $url, null, $headers);
    }

    public function post(string $url, $body = null, ?array $headers = null): Resolver
    {
        return $this->request('post', $url, $body, $headers);
    }

    public function addInterceptor(string $interceptor): void
    {
        $this->interceptors[] = $interceptor;
    }

    public function withInterceptor(string $interceptor): self
    {
        $newHttp = new HttpClient($this->platform);
        $newHttp->addInterceptor($interceptor);
        return $newHttp;
    }
}
