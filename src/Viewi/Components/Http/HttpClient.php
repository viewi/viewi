<?php

namespace Viewi\Components\Http;

use Exception;
use Viewi\Bridge\IViewiBridge;
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

    public function __construct(private Platform $platform, private IViewiBridge $bridge)
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
                        $data = null;
                        $response = null;
                        $dataKey = json_encode($request->body);
                        $requestKey = "{$request->method}_{$request->url}_$dataKey";
                        // Helpers::debug(['calling', $request]);
                        $components = parse_url($request->url);
                        $isExternal = !empty($components['host']);
                        $currentEngine = $this->platform->engine();
                        if ($isExternal) {
                            $request->markAsExternal();
                            $data = $this->bridge->request($request, $currentEngine);
                        } else {
                            $publicLocation = $this->platform->app()->getConfig()->publicPath;
                            $requestedFile = $publicLocation .= $request->url;

                            if ($this->bridge->file_exists($requestedFile) && !$this->bridge->is_dir($requestedFile)) {
                                // file
                                $data = $this->bridge->file_get_contents($requestedFile);
                                $this->platform->httpState[$requestKey] = json_encode('');
                                $response = new Response('/', 200, 'OK', [], $data);
                                $this->interceptResponse($response, $callback, $interceptorInstances);
                                return;
                            }

                            $nextRequestUrl = $request->url;
                            $currentRequestUrl = $currentEngine->getRequest()->url;
                            if ($request->url === $currentRequestUrl) {
                                // recursion, inf loop
                                throw new Exception("Infinite loop detected by requesting URL: $nextRequestUrl");
                            }
                            $data = $this->bridge->request($request, $currentEngine);
                            if ($data instanceof Response) {
                                $response = $data;
                                $data = $data->body;
                            }
                        }
                        $this->platform->httpState[$requestKey] = json_encode($data);
                        // continue to response handler
                        $response = $response ?? new Response('/', 200, 'OK', [], $data);
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

    public function put(string $url, $body = null, ?array $headers = null): Resolver
    {
        return $this->request('put', $url, $body, $headers);
    }

    public function delete(string $url, $body = null, ?array $headers = null): Resolver
    {
        return $this->request('delete', $url, $body, $headers);
    }

    public function patch(string $url, $body = null, ?array $headers = null): Resolver
    {
        return $this->request('post', $url, $body, $headers);
    }

    public function addInterceptor(string $interceptor): void
    {
        $this->interceptors[] = $interceptor;
    }

    public function setInterceptors(array $interceptors): void
    {
        $this->interceptors = $interceptors;
    }

    public function withInterceptor(string $interceptor): self
    {
        $newHttp = new HttpClient($this->platform, $this->bridge);
        $newHttp->setInterceptors($this->interceptors);
        $newHttp->addInterceptor($interceptor);
        return $newHttp;
    }
}
