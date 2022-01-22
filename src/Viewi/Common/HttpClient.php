<?php

namespace Viewi\Common;

use Exception;
use Viewi\Components\Services\AsyncStateManager;
use Viewi\Routing\Route;
use Viewi\WebComponents\Response;

class HttpClient
{
    public array $interceptors = [];
    public array $options = [];
    private $resolve;
    private $reject;
    private $scopeResponses = [];
    private AsyncStateManager $asyncStateManager;

    public function __construct(AsyncStateManager $asyncStateManager)
    {
        $this->asyncStateManager = $asyncStateManager;
    }

    public function getScopeResponses()
    {
        return $this->scopeResponses;
    }

    public function request($type, $url, $data = null, ?array $options = null)
    {

        // intercept(HttpHandler $handler) 
        // { 
        //   // before
        //   $handler->handle(($next) => { 
        //                           // after
        //                           $next(); 
        //                           });
        // }
        $dataKey = json_encode($data);
        $requestKey = "{$type}_{$url}_$dataKey";
        $response = new HttpResponse();
        $requestResolver = function (callable $resolve, callable $reject) use ($type, $url, $data, $response, $requestKey) {
            $query = parse_url($url, PHP_URL_QUERY);
            if ($query) {
                $queryData = [];
                parse_str($query, $queryData);
                if ($data === null) {
                    $data = [];
                }
                $data = array_merge($data, $queryData);
            }
            $data = Route::handle($type, $url, $data);
            if ($data instanceof PromiseResolver) {
                $data->then(function ($data) use ($resolve, $requestKey) {
                    $this->scopeResponses[$requestKey] = $data;
                    $resolve($data);
                }, $reject);
                return;
            }
            if ($data instanceof Response) {
                $response->content = $data->Content;
                $response->headers = $data->Headers;
                $response->status = $data->StatusCode;
                if ($data->StatusCode >= 400 || $data->StatusCode < 200) {
                    $response->success = false;
                    $reject(new Exception("Error getting the response"));
                    return;
                }
                $this->scopeResponses[$requestKey] = $data->Content;
                $resolve($data->Content);
                return;
            }
            // echo " == request $type $url == \n<br>";
            $response->status = 200;
            $response->content = $data;
            $this->scopeResponses[$requestKey] = $data;
            $resolve($data);
        };

        $count = count($this->interceptors);
        if ($count > 0) {

            $onHandle = function () use ($requestResolver) {
                $requestResolver(function () {
                }, function () {
                });
            };

            $previousHandler = null;
            for ($i = $count - 1; $i >= 0; $i--) {
                $httpHandler = new HttpHandler();
                $httpHandler->response = $response;
                $httpHandler->top = $i === 0;
                $httpHandler->previousHandler = $previousHandler;
                $previousHandler = $httpHandler;
                $httpHandler->httpClient = $this;

                $httpHandler->onHandle = $onHandle;

                $interceptor = $this->interceptors[$i][0];
                $method = $this->interceptors[$i][1];

                // action before
                // $handler->handle(...)
                // next handler or request => response
                $onHandle = function () use ($httpHandler, $interceptor, $method) {
                    $interceptor->$method($httpHandler);

                    if ($httpHandler->after !== null && ($httpHandler->previousHandler === null || $httpHandler->previousHandler->continue)) {
                        ($httpHandler->after)(function () use ($httpHandler) {
                            $httpHandler->continue = true;
                            if ($httpHandler->top) {
                                // echo ' --RESOLVING TOP-- ';
                                if ($httpHandler->response->success) {
                                    ($this->resolve)($httpHandler->response->content);
                                } else {
                                    ($this->reject)($httpHandler->response->content);
                                }
                            }
                        });
                    }
                };
            }
            return new PromiseResolver(function (callable $resolve, callable $reject) use ($onHandle) {
                $this->resolve = $resolve;
                $this->reject = $reject;
                $onHandle();
            });
        }

        // ?? track automatically all promises ??
        return $this->asyncStateManager->track(new PromiseResolver($requestResolver), 'http');
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

    public function with(callable $interceptor)
    {
        $client = new HttpClient($this->asyncStateManager);
        $client->interceptors = $this->interceptors;
        $client->interceptors[] = $interceptor;
        return $client;
    }

    public function setOptions(array $options)
    {
        $this->options = array_merge_recursive($this->options, $options);
    }
}
