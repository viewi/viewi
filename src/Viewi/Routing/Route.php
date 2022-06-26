<?php

namespace Viewi\Routing;

use Exception;

class Route
{

    /**
     * 
     * @var array<RouteItem>
     */
    protected static array $routes = [];

    /**
     * 
     * @var RouteAdapterBase|null
     */
    protected static ?RouteAdapterBase $adapter = null;

    public static function getRoutes(): array
    {
        return self::$routes;
    }

    public static function setAdapter(RouteAdapterBase $adapter)
    {
        self::$adapter = $adapter;
    }

    /**
     * @throws Exception
     */
    public static function handle(string $method, string $url, $data = null)
    {
        return self::$adapter
            ? self::$adapter->handle($method, $url, $data)
            : Router::handle($url, $method, $data ?? []);
    }

    public static function add(string $method, string $url, $component, ?array $defaults = null): RouteItem
    {
        $item = new RouteItem(
            $method,
            $url,
            $component,
            is_string($component) && strpos($component, '\\') !== false ?
                substr(strrchr($component, "\\"), 1)
                : $component,
            $defaults
        );
        self::$routes[] = $item;
        self::$adapter && self::$adapter->register($method, $url, $component, $defaults);
        return $item;
    }

    public static function all(string $url, string $component, ?array $defaults = null)
    {
        self::add(
            '*',
            $url,
            $component,
            $defaults
        );
    }

    public static function get(string $url, string $component, ?array $defaults = null): RouteItem
    {
        return self::add(
            'get',
            $url,
            $component,
            $defaults
        );
    }

    public static function post(string $url, string $component, ?array $defaults = null): RouteItem
    {
        return self::add(
            'post',
            $url,
            $component,
            $defaults
        );
    }

    public static function put(string $url, string $component, ?array $defaults = null): RouteItem
    {
        return self::add(
            'put',
            $url,
            $component,
            $defaults
        );
    }

    public static function delete(string $url, string $component, ?array $defaults = null): RouteItem
    {
        return self::add(
            'delete',
            $url,
            $component,
            $defaults
        );
    }

    public static function patch(string $url, string $component, ?array $defaults = null): RouteItem
    {
        return self::add(
            'patch',
            $url,
            $component,
            $defaults
        );
    }

    public static function options(string $url, string $component, ?array $defaults = null): RouteItem
    {
        return self::add(
            'options',
            $url,
            $component,
            $defaults
        );
    }
}
