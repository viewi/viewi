<?php

namespace Viewi;

use Exception;
use RuntimeException;
use Viewi\Builder\Builder;
use Viewi\Components\Assets\ViewiAssets;
use Viewi\Components\Http\HttpClient;
use Viewi\Container\Factory;
use Viewi\Exceptions\RouteNotFoundException;
use Viewi\Router\ComponentRoute;
use Viewi\Router\Router;

class App
{
    private Router $router;
    private Factory $factory;
    private bool $ready = false;
    private array $meta;

    public function __construct(private string $buildPath)
    {
    }

    public function router(): Router
    {
        return $this->router ?? ($this->router = new Router());
    }

    public function factory(): Factory
    {
        if (!isset($this->factory)) {
            $this->factory = new Factory();
            $this->factory->add(App::class, function (Engine $_) {
                return $this;
            });
            $this->factory->add(ViewiAssets::class, function (Engine $engine) {
                $assets = new ViewiAssets();
                $assets->appPath = $engine->getAssets()['app'];
                $state = [];
                $responses = [];
                /** @var HttpClient */
                $httpClient = $engine->getIfExists(HttpClient::class);
                if ($httpClient !== null) {
                    $responses = $httpClient->getScopeResponses();
                }
                $state['http'] = $responses;
                $state['state'] = $engine->getState();
                $state['state']['ViewiAssets']['appPath'] = $assets->appPath;
                $stateJson = json_encode($state);
                $rawScript = "<script data-keep=\"ViewiAssets\">window.viewiScopeState = {$stateJson};</script>";
                $assets->data = $rawScript;
                return $assets;
            });
        }
        return $this->factory;
    }

    public function engine(): Engine
    {
        if (!$this->ready) {
            $this->meta = require_once $this->buildPath . DIRECTORY_SEPARATOR . 'components.php';
            $this->ready = true;
            $this->factory();
        }
        return new Engine($this->meta, $this->factory);
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

    public function build(string $entryPath, array $includes, string $buildPath, string $jsPath, string $publicPath, string $assetsPath)
    {
        $builder = new Builder($this->router());
        $builder->build($entryPath, $includes, $buildPath, $jsPath, $publicPath, $assetsPath);
    }
}
