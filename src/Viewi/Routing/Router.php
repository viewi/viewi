<?php

namespace Viewi\Routing;

use Exception;

class Router
{
    public static function register($method, $url, $actionOrController)
    {
        return Route::add(
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
            if (is_array($action)) {
                $instance = new $action[0]();
                $method = $action[1];
                $response = $instance->$method($match['params']);
            } else {
                $response = $action(...array_values($match['params']));
            }
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
        $parts = explode('/', trim($url, '/'));
        $method = strtolower($method);
        $routes = Route::getRoutes();
        foreach ($routes as $item) {
            if ($method === $item->method || $item->method === '*') {
                $params = [];
                $valid = true;
                $targetUrl = trim($item->url, '/');
                $targetParts = explode('/', $targetUrl);
                $pi = 0;
                $skipAll = false;
                $count = count($targetParts);
                for ($pi; $pi < $count; $pi++) {
                    $urlExpr = $targetParts[$pi];
                    $hasWildCard = strpos($urlExpr, '*') !== false;
                    if ($hasWildCard) {
                        $beginning = substr($urlExpr, 0, -1);
                        if ($beginning === '' || strpos($parts[$pi], $beginning) === 0) {
                            $skipAll = true;
                            break;
                        }
                    }
                    $hasParams = strpos($urlExpr, '{') !== false;
                    if (
                        (!isset($parts[$pi]) || $urlExpr !== $parts[$pi])
                        && !$hasParams
                    ) {
                        $valid = false;
                        break;
                    }
                    if ($hasParams) {
                        // has {***} parameter
                        $bracketParts = preg_split('/[{}]+/', $urlExpr);
                        // $console->log($urlExpr, $bracketParts);
                        $paramName = $bracketParts[1];
                        if ($paramName[strlen($paramName) - 1] === '?') {
                            // $optional
                            $paramName = substr($paramName, 0, -1);
                        } else if ($pi >= count($parts)) {
                            $valid = false;
                            break;
                        }
                        if (strpos($paramName, '<') !== false) { // has <regex>

                            // print_r($paramName);
                            preg_match('/<([^>]+)>/', $paramName, $matches);
                            $paramName = preg_replace('/<([^>]+)>/', '', $paramName);

                            // print_r($matches);
                            $item->wheres[$paramName] = $matches[1];
                        }
                        if (isset($item->wheres[$paramName])) {
                            $regex = '/' . $item->wheres[$paramName] . '/';
                            // echo $paramName . $parts[$pi];
                            if (preg_match($regex, $parts[$pi]) === 0) {
                                $valid = false;
                                break;
                            }
                            // test for "/"
                            if (preg_match($regex, '/') === 1) { // skip to the end
                                $skipAll = true;
                                // echo $paramName . $parts[$pi]. $regex;
                            }
                        }
                        $paramValue = $parts[$pi] ?? null;
                        if ($bracketParts[0]) {
                            if (strpos($paramValue, $bracketParts[0]) !== 0) {
                                $valid = false;
                                break;
                            } else {
                                $paramValue = substr($paramValue, strlen($bracketParts[0]));
                            }
                        }
                        $params[$paramName] = $paramValue;
                        if ($skipAll) {
                            $params[$paramName] = implode('/', array_slice($parts, $pi));
                            break;
                        }
                    }
                }
                if ($pi < count($parts) && !$skipAll) {
                    $valid = false;
                }
                if ($valid) {
                    return [
                        'route' => $item,
                        'params' => $params
                    ];
                }
            }
        }

        return null;
    }
}
