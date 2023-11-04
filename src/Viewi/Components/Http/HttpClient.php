<?php

namespace Viewi\Components\Http;

use Exception;
use Viewi\Builder\Attributes\CustomJs;
use Viewi\Components\Callbacks\Resolver;
use Viewi\Components\Environment\Process;
use Viewi\Components\Http\Interceptor\IHttpInterceptor;
use Viewi\Components\Http\Interceptor\RequestHandler;
use Viewi\Components\Http\Interceptor\ResponseHandler;
use Viewi\Components\Http\Message\Request;
use Viewi\Components\Http\Message\Response;
use Viewi\DI\Singleton;
use Viewi\Engine;
use Viewi\Helpers;

#[Singleton]
#[CustomJs]
class HttpClient
{
    /**
     * 
     * @var array
     */
    private array $interceptors = [];

    public function __construct(private Process $process)
    {
    }

    public function getScopeResponses()
    {
        return $this->process->httpState;
    }

    public function request(string $method, string $url, $body = null, ?array $headers = null): Resolver
    {
        $request = new Request($url, $method, $headers ?? [], $body);
        $resolver = new Resolver(function (callable $callback) use ($request) {
            try {
                $onHandle = function (Request $request, array $interceptorInstances) use ($callback) {
                    $dataKey = json_encode($request->body);
                    $requestKey = "{$request->method}_{$request->url}_$dataKey";
                    // Helpers::debug(['calling', $request]);
                    $data = $this->process->app()->run($request->url, $request->method);
                    $this->process->httpState[$requestKey] = json_encode($data);
                    // continue to response handler
                    $this->interceptResponse($data, $callback, $interceptorInstances);
                };
                $requestHandler = new RequestHandler($onHandle, $this->process->engine(), $this->interceptors, $request);
                $requestHandler->next($request);
            } catch (Exception $ex) {
                $callback(null, $ex);
            }
        });
        return $resolver;
    }

    private function interceptResponse($responseData, $callback, array $interceptorInstances)
    {
        $onHandle = function (Response $response) use ($callback) {
            $callback($response->body);
        };
        $response = new Response('/', 200, 'OK', [], $responseData);
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
        $newHttp = new HttpClient($this->process);
        $newHttp->addInterceptor($interceptor);
        return $newHttp;
    }
}
