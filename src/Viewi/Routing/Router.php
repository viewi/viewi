<?php

namespace Viewi\Routing;

use Exception;
use ReflectionMethod;
use RuntimeException;
use Viewi\Common\JsonMapper;

class Router
{
    public static function register(string $method, string $url, $actionOrController)
    {
        return Route::add(
            $method,
            $url,
            $actionOrController
        );
    }

    public static function handle(string $url, string $method = 'get', array $params = [])
    {
        $match = self::resolve(explode('?', $url)[0], $method);

        if ($match === null) {
            throw new Exception('No route was matched!');
        }

        // print_r($match);
        $action = $match['route']->action;

        if (is_array($action)) {
            $instance = new $action[0]();
            $method = $action[1];

            $r = new ReflectionMethod($action[0], $method);
            $arguments = $r->getParameters();
            // print_r($arguments);
            $inputs = [];
            $stdObject = null;
            $binaryData = false;
            $inputContent = null;
            if (count($arguments) > 0) {
                $inputContent = file_get_contents('php://input');
                $stdObject = json_decode($inputContent, false);

                if ($inputContent && $stdObject === null) {
                    // binary data
                    $binaryData = true;
                }
            }
            foreach ($arguments as $argument) {
                $argName = $argument->getName();
                $argumentValue = $match['params'][$argName] ?? ($params[$argName] ?? null);
                // parse json body
                if ($argumentValue === null && $stdObject !== null) {
                    if ($argument->hasType() && !$argument->getType()->isBuiltin()) {
                        $typeName = $argument->getType()->getName();
                        if (class_exists($typeName)) {
                            $argumentValue = JsonMapper::Instantiate($typeName, $stdObject);
                            // print_r($argumentValue);
                        }
                    } else if (isset($stdObject->$argName)) {
                        $argumentValue = $stdObject->$argName;
                    } else if ($argName === 'data') {
                        $argumentValue = $stdObject;
                    }
                }
                if ($argumentValue === null && $binaryData && $argName === 'data') {
                    $argumentValue = $inputContent;
                } else if ($argumentValue === null && $argument->isDefaultValueAvailable()) {
                    $argumentValue = $argument->getDefaultValue();
                }
                $inputs[] = $argumentValue;
            }
            $response = $instance->$method(...$inputs);
        } else if (is_callable($action)) {
            // TODO: match params by name
            $response = $action(...array_values($match['params'] + $params));
        } else {
            $instance = new $action();

            if (!is_callable($instance)) {
                $classNS = get_class($instance);
                throw new RuntimeException("Component '$classNS' must be callable");
            }

            $response = $instance($match['params']);
        }

        return $response;
    }

    /**
     *
     * @param mixed $url
     * @param string $method
     * @return array{route: RouteItem, params: array}
     */
    public static function resolve(string $url, string $method = 'get'): ?array
    {
        if (empty($url)) {
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
