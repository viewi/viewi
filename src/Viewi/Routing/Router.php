<?php

namespace Viewi\Routing;

use Exception;

class Router
{
    public static function register($method, $url, $actionOrController)
    {
        Route::add(
            $method,
            $url,
            $actionOrController
        );
    }

    public static function handle($url, $method = 'get')
    {
        $match = self::resolve($url, $method);
        if ($match === null) {
            throw new Exception('No route was matched!');
        }
        // print_r($match);
        $action = $match['route']->action;
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
        $routes = Route::getRoutes();
        foreach ($routes as $route) {
            if ($method === $route->method) {
                if ($route->url === '*') {
                    return [
                        'route' => $route,
                        'params' => []
                    ];
                }
                $fragments = explode('/', $route->url);
                $count = count($fragments);
                if ($count === count($parts)) {
                    $params = [];
                    $valid = true;
                    for ($i = 0; $i < $count; $i++) {
                        if ($fragments[$i] && $fragments[$i][0] === '{') {
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
