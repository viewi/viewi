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
    private $httpResolve;
    private $httpReject;
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
            $handledResponse = Route::handle($type, $url, $data);

            $responseResolver = function ($data) use ($resolve, $requestKey, $response, $reject) {
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

            if ($handledResponse instanceof PromiseResolver) {
                $handledResponse->then($responseResolver, $reject);
                return;
            }
            $responseResolver($handledResponse);
        };

        $count = count($this->interceptors);
        if ($count > 0) {

            // intercept(HttpHandler $handler) {
            //      $handler->handle(
            //          function ($next) use ($handler) {
            //              $next(); // next interceptor or http call -> promise(mainResolve, mainReject)
            //      $handler->reject($error); // main reject()
            // -- NEW ASYNC INTERCEPTORS --

            $mainResolve = null;
            $mainReject = null;

            $handlerIndex = -1;
            $handleIteration = null;

            $afterIndex = -2;
            $afters = [];
            $handleAfters = null;
            $handleAfters = function () use (&$mainReject, &$mainResolve, $requestResolver, &$afters, &$afterIndex, &$handleAfters, $response) {
                $afterIndex++;
                // echo "Handling after $afterIndex\n";

                if ($afterIndex === -1) {
                    $requestResolver(
                        //$mainResolve
                        function () use ($handleAfters) {
                            // echo "Request call resolved, calling afters chain\n";
                            // print_r($handleAfters);
                            $handleAfters();
                        },
                        $mainReject
                    );
                } else if ($afterIndex < count($afters)) {
                    // echo "Calling after $afterIndex\n";
                    $afterFunction = $afters[$afterIndex];
                    $afterFunction($handleAfters);
                } else {
                    // echo "Calling main resolve reject\n";
                    // print_r($response);
                    if ($response->success) {
                        $mainResolve($response->content);
                    } else {
                        $mainReject($response->content);
                    }
                }
            };

            $handleIteration = function () use (&$handlerIndex, $response, &$mainReject, &$mainResolve, &$handleIteration, &$afters, $handleAfters) {
                $handlerIndex++;
                $count = count($this->interceptors);
                if ($handlerIndex < $count) {
                    // call down
                    $httpHandler = new HttpHandler();
                    $httpHandler->response = $response;
                    $httpHandler->httpClient = $this;
                    $httpHandler->onHandle = function () use ($handleIteration, $httpHandler, &$afters) {
                        $afters[] = $httpHandler->after;
                        $handleIteration();
                    };
                    $httpHandler->onReject = function ($error) use ($mainReject) {
                        $mainReject($error);
                    };

                    $interceptor = $this->interceptors[$handlerIndex][0];
                    $method = $this->interceptors[$handlerIndex][1];
                    $interceptor->$method($httpHandler);
                } else {
                    // call http, call up
                    // print_r(['simulate http request', !!$mainResolve, !!$mainReject]);
                    // chain afters
                    $afters = array_reverse($afters);
                    $handleAfters();
                }
            };

            return $this->asyncStateManager->track(new PromiseResolver(function (callable $resolve, callable $reject) use (&$mainResolve, &$mainReject, $handleIteration) {
                $mainResolve = $resolve;
                $mainReject = $reject;
                // print_r(['track handleIteration', !!$mainResolve, !!$mainReject]);
                $handleIteration();
            }));
        }

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
        $client->scopeResponses = &$this->scopeResponses;
        return $client;
    }

    public function setOptions(array $options)
    {
        $this->options = array_merge_recursive($this->options, $options);
    }
}
