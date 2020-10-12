<?php

namespace Viewi\Routing;

include 'RouteItem.php';
include 'RouteAdapterBase.php';

class Route
{

    /**
     * 
     * @var array<RouteItem>
     */
    public static array $routes = [];

    /**
     * 
     * @var RouteAdapterBase
     */
    public static RouteAdapterBase $adapter;

    public static function setAdapter(RouteAdapterBase $adapter)
    {
        self::$adapter = $adapter;
    }

    public static function handle(string $method, string $url)
    {
        return self::$adapter->handle($method, $url);
    }

    public static function add(string $method, string $url, string $component, ?array $defaults = null)
    {
        self::$routes[] = new RouteItem(
            $method,
            $url,
            $component,
            $defaults
        );
        self::$adapter->register($method, $url, $component, $defaults);
    }

    public static function get(string $url, string $component, ?array $defaults = null)
    {
        self::add(
            'get',
            $url,
            $component,
            $defaults
        );
    }

    public static function post(string $url, string $component, ?array $defaults = null)
    {
        self::add(
            'post',
            $url,
            $component,
            $defaults
        );
    }

    public static function put(string $url, string $component, ?array $defaults = null)
    {
        self::add(
            'put',
            $url,
            $component,
            $defaults
        );
    }

    public static function delete(string $url, string $component, ?array $defaults = null)
    {
        self::add(
            'delete',
            $url,
            $component,
            $defaults
        );
    }

    public static function patch(string $url, string $component, ?array $defaults = null)
    {
        self::add(
            'patch',
            $url,
            $component,
            $defaults
        );
    }

    public static function options(string $url, string $component, ?array $defaults = null)
    {
        self::add(
            'options',
            $url,
            $component,
            $defaults
        );
    }
}
