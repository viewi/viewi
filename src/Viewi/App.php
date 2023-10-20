<?php

namespace Viewi;

use Exception;
use RuntimeException;
use Viewi\Builder\Builder;
use Viewi\Exceptions\RouteNotFoundException;
use Viewi\Router\ComponentRoute;
use Viewi\Router\Router;

class App
{
    private Router $router;
    private Engine $engine;

    public function __construct(private string $buildPath)
    {
    }

    public function router(): Router
    {
        return $this->router ?? ($this->router = new Router());
    }

    public function engine(): Engine
    {
        return $this->engine ?? ($this->engine = new Engine($this->buildPath));
    }

    // TODO: adapter, PSR request/response, framework handler
    public function run(?string $url = null, string $method = null)
    {
        $url ??= $_SERVER['REDIRECT_URL'] ?? preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
        $method ??= $_SERVER['REQUEST_METHOD'];
        $match = $this->router->resolve(explode('?', $url)[0], $method);
        // Helpers::debug([$match, $this->router]);
        if ($match === null) {
            $routeData = [
                'path' => $url,
                'method' => $method
            ];
            throw new RouteNotFoundException("Route \"$url\" not found", 0, null, $routeData);
        }

        $action = $match['item']->action;
        if ($action instanceof ComponentRoute) {
            return $this->engine()->render($action->component, $match['params']);
        } elseif (is_array($action)) {
            throw new Exception("Not implemented");
        } elseif (is_callable($action)) {
            return $action(...array_values($match['params']));
        } else {
            $instance = new $action();
            if (!is_callable($instance)) {
                $classNS = get_class($instance);
                throw new RuntimeException("Component '$classNS' must be callable");
            }
            return $instance($match['params']);
        }
    }

    public function build(string $entryPath, array $includes, string $buildPath, string $jsPath, string $publicPath)
    {
        $builder = new Builder($this->router());
        $builder->build($entryPath, $includes, $buildPath, $jsPath, $publicPath);
    }
}
