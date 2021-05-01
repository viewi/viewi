<?php

namespace Viewi\Common;

use Exception;
use Viewi\Routing\Route;
use Viewi\WebComponents\Response;

class HttpClient
{
    public array $interceptors = [];
    public array $options = [];

    public function request($type, $url, $data = null, ?array $options = null)
    {
        $onSuccess = function () use ($type, $url, $data) {
            $data = Route::handle($type, $url, $data);
            if ($data instanceof Response) {
                if ($data->StatusCode >= 300 || $data->StatusCode <= 300) {
                    throw new Exception("Error getting the response");
                }
                return $data->Content;
            }
            return $data;
        };
        $count = count($this->interceptors);
        if ($count > 0) {
            for ($i = $count - 1; $i >= 0; $i--) {
                $httpMiddleware = $this->interceptors[$i][0];
                $method = $this->interceptors[$i][1];
                $httpMiddleware->$method($this, function () {
                    // nothing
                }, function () {
                    // nothing
                });
            }
        }

        $resolver = new PromiseResolver($onSuccess);

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

    public function with(callable $interceptor)
    {
        $client = new HttpClient();
        $client->interceptors = $this->interceptors;
        $client->interceptors[] = $interceptor;
        return $client;
    }

    public function setOptions(array $options)
    {
        $this->options = array_merge_recursive($this->options, $options);
    }
}
