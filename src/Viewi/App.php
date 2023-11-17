<?php

namespace Viewi;

use Exception;
use RuntimeException;
use Viewi\Builder\Builder;
use Viewi\Components\Assets\ViewiAssets;
use Viewi\Components\Environment\Process;
use Viewi\Components\Http\HttpClient;
use Viewi\Components\Http\Message\Response;
use Viewi\Container\Factory;
use Viewi\Exceptions\RouteNotFoundException;
use Viewi\Router\ComponentRoute;
use Viewi\Router\RouteItem;
use Viewi\Router\Router;

class App
{
    private Router $router;
    private Factory $factory;
    private bool $ready = false;
    private array $meta;

    public function __construct(private AppConfig $config, private array $publicConfig = [])
    {
    }

    public function getPublicConfig(): array
    {
        return $this->publicConfig;
    }

    public function router(): Router
    {
        return $this->router ?? ($this->router = new Router());
    }

    public function factory(): Factory
    {
        if (!isset($this->factory)) {
            $this->factory = new Factory();
            $this->factory->add(Process::class, function (Engine $engine) {
                return new Process($this, $engine);
            });
            $this->factory->add(ViewiAssets::class, function (Engine $engine) {
                $assets = new ViewiAssets();
                $assetsMeta = $engine->getAssets();
                $appendVersion = $assetsMeta['append-version'];
                $appPath = $assetsMeta['minify'] ? $assetsMeta['app-min'] : $assetsMeta['app'];
                if ($appendVersion) {
                    $appPath .= '?' . $assetsMeta['build-id'];
                }
                $assets->appPath = $appPath;
                $state = [];
                $responses = [];
                /** @var Process */
                $process = $engine->getIfExists(Process::class);
                if ($process !== null) {
                    $responses = $process->httpState;
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
            if ($this->config->devMode) {
                $this->build();
            }
            $this->meta = require_once $this->config->buildPath . DIRECTORY_SEPARATOR . 'components.php';
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

        /** @var RouteItem */
        $routeItem =  $match['item'];
        $action = $routeItem->action;
        $response = null;
        if ($action instanceof ComponentRoute) {
            $response = $this->engine()->render($action->component, $match['params']);
        } elseif (is_array($action)) {
            throw new Exception("Not implemented");
        } elseif (is_callable($action)) {
            $response = $action(...array_values($match['params']));
        } else {
            $instance = new $action();
            if (!is_callable($instance)) {
                $classNS = get_class($instance);
                throw new RuntimeException("Component '$classNS' must be callable");
            }
            $response = $instance($match['params']);
        }
        if ($routeItem->transformCallback !== null && $response instanceof Response) {
            $response = ($routeItem->transformCallback)($response);
        }
        return $response;
    }

    public function build()
    {
        $builder = new Builder($this->router());
        $builder->build($this->config, $this->publicConfig);
    }
}
