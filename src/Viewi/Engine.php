<?php

namespace Viewi;

use Exception;
use Viewi\Components\BaseComponent;
use Viewi\Components\Context\ProvidesScope;
use Viewi\Components\Http\Message\Request;
use Viewi\Components\Http\Message\Response;
use Viewi\Components\Middleware\IMIddleware;
use Viewi\Components\Middleware\MIddlewareContext;
use Viewi\Components\Render\IRenderable;
use Viewi\Components\Render\RenderContext;
use Viewi\Container\Factory;
use Viewi\DI\Scope;
use Viewi\DI\Scoped;
use Viewi\DI\Singleton;

class Engine
{
    private array $DIContainer = [];
    private int $instanceIdCounter = 0;
    private bool $allow = true;
    private Response $response;
    private ?Request $request;
    /**
     * 
     * @var callable[]
     */
    private array $postActions = [];
    private int $postActionIdCounter = 0;
    private ProvidesScope $provides;
    private ?BaseComponent $currentInstance = null;

    public function __construct(private AppConfig $config, private array $meta, private Factory $factory)
    {
        $this->provides = new ProvidesScope();
    }

    public function render(string $component, array $params = [], ?Request $request = null): Response
    {
        $component = strpos($component, '\\') !== false ?
            substr(strrchr($component, "\\"), 1)
            : $component;
        $this->request = $request;
        if (isset($this->meta['components'][$component]['middleware'])) {
            $this->guard($this->meta['components'][$component]['middleware']);
        }
        $response = $this->getResponse();
        if ($this->allow) {
            $content = $this->renderComponent($component, null,  [], [], [], $params);
            $response->headers['Content-type'] = 'text/html; charset=utf-8';
            $response->body = $content;
        } else {
            $response->status = isset($response->headers['Location']) ? 302 : 403;
            $response->statusText = 'Forbidden';
            $response->body = 'Forbidden';
        }
        foreach ($this->postActions as $postAction) {
            $postAction($response);
        }
        return $response;
    }

    public function getResponse(): Response
    {
        return $this->response ?? ($this->response = new Response($this->request?->url ?? '/', 200, 'OK'));
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }

    public function guard(array $middlewareList): void
    {
        $next = new MIddlewareContext(function (bool $allow = true) {
            $this->allow = $allow;
        });
        foreach ($middlewareList as $middlewareName) {
            if ($this->allow) {
                /**
                 * @var IMIddleware $middleware
                 */
                $middleware = $this->resolve($middlewareName);
                $middleware->run($next);
            }
        }
    }

    public function renderSlot($component, $scope, $slotFunc, $parentSlots)
    {
        return $slotFunc($this, $component, $parentSlots, $scope);
    }

    public function renderComponent(string $component, ?BaseComponent $parentComponent, array $props, array $slots, array $scope, array $params = [])
    {
        // if ($component === 'Portal') {
        //     Helpers::debug([$component, 'props', $props, 'slots', $slots, 'scope', $scope, 'params', $params]);
        // }
        // print_r([$component, 'props', $props, 'slots', $slots, 'scope', $scope, 'params', $params]);
        // print_r([$component, 'scope', $parentComponent ? get_class($parentComponent) : 'root']);
        if (
            !isset($this->meta['components'][$component])
        ) {
            throw new Exception("Component '$component' not found.");
        }
        // Helpers::debug([$componentMeta]);
        /**
         * @var BaseComponent $classInstance
         */
        $classInstance = $this->resolve($component, $params);
        // print_r([$component, $classInstance->_parent ? get_class($classInstance->_parent) : 'NULL']);
        $previousInstance = $this->currentInstance;
        $this->currentInstance = $classInstance;
        if ($classInstance instanceof IRenderable) {
            return $classInstance->render(new RenderContext($component, $props, $slots, $scope, $params));
        }
        /**
         * @var array{inputs: array, components: array}
         */
        $componentMeta = $this->meta['components'][$component];
        if (isset($componentMeta['hooks']['init'])) {
            /**
             * @var mixed $classInstance
             */
            $classInstance->init();
        }
        if (
            !isset($this->meta['components'][$component]['Function'])
        ) {
            throw new Exception("Component '$component' not found.");
        }
        include_once $this->config->buildPath . DIRECTORY_SEPARATOR . $componentMeta['Path'];
        $renderFunc = $componentMeta['Function'];
        // Helpers::debug([$props, $componentMeta]);
        foreach ($props as $key => $inputValue) {
            if ($key === '_props') {
                // passing props as object (array)
                foreach ($inputValue as $propKey => $propInputValue) {
                    if (isset($componentMeta['inputs'][$propKey])) {
                        $classInstance->{$propKey} = $propInputValue;
                    }
                    $classInstance->_props[$propKey] = $propInputValue;
                }
                // $this->debug(['all props', $inputValue, $classInstance]);
            } else {
                if ($key === 'model') {
                    if (isset($componentMeta['inputs']['modelValue'])) {
                        $classInstance->modelValue = $inputValue;
                    }
                } else {
                    if (isset($componentMeta['inputs'][$key])) {
                        $classInstance->{$key} = $inputValue;
                    }
                    $classInstance->_props[$key] = $inputValue;
                }
            }
        }
        if (isset($componentMeta['hooks']['mounted'])) {
            $classInstance->mounted();
        }
        $content = $renderFunc($this, $classInstance, $slots, $scope);
        $this->currentInstance = $previousInstance;
        return $content;
    }

    public function isComponent(string $name)
    {
        return isset($this->meta['components'][$name]);
    }

    public function getAssets()
    {
        return $this->meta['assets'];
    }

    public function getState(): array
    {
        return $this->DIContainer['state'] ?? [];
    }

    public function getMetadata(string $name)
    {
        $name = strpos($name, '\\') !== false ?
            substr(strrchr($name, "\\"), 1)
            : $name;
        if (!isset($this->meta['components'][$name])) {
            throw new Exception("Metadata for type '$name' does not exist.");
        }
        return $this->meta['components'][$name];
    }

    public function shortName(string $name): string
    {
        return strpos($name, '\\') !== false ?
            substr(strrchr($name, "\\"), 1)
            : $name;
    }

    public function getIfExists(string $name)
    {
        if (isset($this->DIContainer[$name])) {
            return $this->DIContainer[$name];
        }
        return null;
    }

    public function set(string $name, $mixed)
    {
        $this->DIContainer[$name] = $mixed;
    }

    /**
     * 
     * @param array{inputs: array, components: array} $componentMeta 
     * @return mixed 
     */
    public function resolve(string $name, array $params = [], bool $canBeNull = false)
    {
        if (!isset($this->meta['components'][$name])) {
            if ($this->factory->has($name)) {
                $constructor = $this->factory->get($name);
                return $constructor($this);
            } else {
                if ($canBeNull) {
                    return null;
                }
                throw new Exception("Can not resolve instance for type '$name'.");
            }
        }
        $componentMeta = $this->meta['components'][$name];
        $fullClassName = $componentMeta['Namespace'] . '\\' . $componentMeta['Name'];
        $instance = false;
        $storeInContainer = false;
        if (isset($componentMeta['di'])) {
            switch ($componentMeta['di']) {
                case Scoped::NAME: // Scoped and Singleton are the same on back-end (SSR)
                case Singleton::NAME: {
                        if (isset($this->DIContainer[$fullClassName])) {
                            return $this->DIContainer[$fullClassName];
                        }
                        $storeInContainer = true;
                        break;
                    }
                default: // skip
                    break;
            }
        }
        $provides = [];
        if ($this->factory->has($name)) {
            $constructor = $this->factory->get($name);
            $instance = $constructor($this);
        } elseif (empty($componentMeta['dependencies'])) {
            $instance = new $fullClassName();
        } else {
            $arguments = [];
            foreach ($componentMeta['dependencies'] as $argName => $type) {
                // resolve router param
                $canBeNull = isset($type['null']);
                $diType = $type['di'] ?? false;
                if ($diType === Scope::PARENT) {
                    $arguments[] = $this->currentInstance->inject($type['name']);
                } elseif (isset($params[$argName])) {
                    $arguments[] = in_array($type['name'], ['int', 'float'])
                        ? (float)$params[$argName]
                        : $params[$argName];
                } elseif (isset($type['default'])) {
                    $arguments[] = $type['default'];
                } elseif (isset($type['builtIn'])) {
                    switch ($type['name']) { // TODO: more types
                        case 'string': {
                                $arguments[] = '';
                                break;
                            }
                        case 'int': {
                                $arguments[] = 0;
                                break;
                            }
                        default: {
                                throw new Exception("Type '{$type['name']}' is not configured.");
                                break;
                            }
                    }
                } else {
                    $arguments[] = $this->resolve($type['name'], [], $canBeNull);
                }
                if ($diType === Scope::COMPONENT) {
                    $provides[$type['name']] = $arguments[count($arguments) - 1];
                }
            }
            $instance = new $fullClassName(...$arguments);
        }
        if ($storeInContainer) {
            $this->DIContainer[$fullClassName] = $instance;
        }
        if (isset($componentMeta['base'])) {
            /**
             * @var BaseComponent $instance
             */
            $instance->__id = ++$this->instanceIdCounter;
            $instance->_provides = $this->currentInstance !== null ? $this->currentInstance->_provides : $this->provides;
            $instance->_parent = $this->currentInstance;
            foreach ($provides as $key => $value) {
                $instance->provide($key, $value);
            }
        }
        // Preserve
        if (isset($componentMeta['preserve'])) {
            foreach ($componentMeta['preserve'] as $prop => $_) {
                if (!isset($this->DIContainer['state'])) {
                    $this->DIContainer['state'] = [];
                }
                $this->DIContainer['state'][$name][$prop] = $instance->{$prop};
            }
        }
        return $instance;
    }

    /**
     * 
     * @param callable $action 
     * @param null|string $id 
     * @param bool $override 
     * @return string 
     * @throws Exception 
     */
    public function schedulePostAction($action, ?string $id = null, bool $override = false): string
    {
        if ($id === null) {
            $id = 'postAction' . ($this->postActionIdCounter++);
        }
        if (!$override && $this->postActionExists($id)) {
            throw new Exception("Post action with id '$id' exists already.");
        }

        $this->postActions[$id] = $action;
        return $id;
    }

    public function postActionExists(string $id): bool
    {
        return isset($this->postActions[$id]);
    }
}
