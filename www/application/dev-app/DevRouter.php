<?php

namespace DevApp;

use Exception;

class DevRouter
{
    // $_SERVER['REQUEST_METHOD']
    // $_SERVER['REDIRECT_URL'];
    public static array $routes = [];
    public static function register($method, $url, $actionOrController)
    {
        self::$routes[] = [
            'method' => $method,
            'url' => $url,
            'action' => $actionOrController
        ];
    }

    public static function handle($url, $method = 'get')
    {
        $match = DevRouter::resolve($url, $method);
        if ($match === null) {
            throw new Exception('No route was matched!');
        }
        // print_r($match);
        $action = $match['route']['action'];
        $response = '';
        if (is_callable($action)) {
            $response = $action(...array_values($match['params']));
        } else {
            $instance = new $action();
            $response = $instance($match['params']);
        }
        return $response;
    }

    public static function resolve($url, $method = 'get'): ?array
    {
        if (!$url) {
            $url = '/';
        }
        $parts = explode('/', $url);
        $method = strtolower($method);
        foreach (self::$routes as $route) {
            if ($method === $route['method']) {
                if ($route['url'] === '*') {
                    return [
                        'route' => $route,
                        'params' => []
                    ];
                }
                $fragments = explode('/', $route['url']);
                $count = count($fragments);
                if ($count === count($parts)) {
                    $params = [];
                    $valid = true;
                    for ($i = 0; $i < $count; $i++) {
                        if ($fragments[$i][0] === '{') {
                            $key = substr($fragments[$i], 1, strlen($fragments[$i]) - 2);
                            $params[$key] = $parts[$i];
                        } else if ($fragments[$i] !== $parts[$i]) {
                            $valid = false;
                            break;
                        }
                    }
                    if ($valid) {
                        return [
                            'route' => $route,
                            'params' => $params
                        ];
                    }
                }
            }
        }

        return null;
    }
}
