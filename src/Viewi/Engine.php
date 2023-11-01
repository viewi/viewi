<?php

namespace Viewi;

use Exception;
use Viewi\Components\BaseComponent;
use Viewi\Components\Middleware\IMIddleware;
use Viewi\Components\Middleware\MIddlewareContext;
use Viewi\Container\Factory;
use Viewi\DI\Scoped;
use Viewi\DI\Singleton;

class Engine
{
    private array $DIContainer = [];
    private int $instanceIdCounter = 0;
    private bool $allow = true;

    public function __construct(private array $meta, private Factory $factory)
    {
    }

    public function render(string $component, array $params = [])
    {
        $component = strpos($component, '\\') !== false ?
            substr(strrchr($component, "\\"), 1)
            : $component;
        if (isset($this->meta['components'][$component]['middleware'])) {
            $this->guard($this->meta['components'][$component]['middleware']);
        }
        if ($this->allow) {
            return $this->renderComponent($component, [], [], [], $params);
        }
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

    public function renderComponent(string $component, array $props, array $slots, array $scope, array $params = [])
    {
        if (
            !isset($this->meta['components'][$component])
            || !isset($this->meta['components'][$component]['Function'])
        ) {
            throw new Exception("Component '$component' not found.");
        }
        // Helpers::debug([$componentMeta]);
        $classInstance = $this->resolve($component, $params);
        /**
         * @var array{inputs: array, components: array}
         */
        $componentMeta = $this->meta['components'][$component];
        if (isset($componentMeta['hooks']['init'])) {
            $classInstance->init();
        }
        include_once $this->meta['buildPath'] . DIRECTORY_SEPARATOR . $componentMeta['Path'];
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
        return $renderFunc($this, $classInstance, $slots, $scope);
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

    public function getIfExists(string $name)
    {
        if (isset($this->DIContainer[$name])) {
            return $this->DIContainer[$name];
        }
        return null;
    }

    /**
     * 
     * @param array{inputs: array, components: array} $componentMeta 
     * @return mixed 
     */
    public function resolve(string $name, array $params = [])
    {
        if (!isset($this->meta['components'][$name])) {
            if ($this->factory->has($name)) {
                $constructor = $this->factory->get($name);
                return $constructor($this);
            } else {
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
        if ($this->factory->has($name)) {
            $constructor = $this->factory->get($name);
            $instance = $constructor($this);
        } elseif (empty($componentMeta['dependencies'])) {
            $instance = new $fullClassName();
        } else {
            $arguments = [];
            foreach ($componentMeta['dependencies'] as $argName => $type) {
                // resolve router param
                if (isset($params[$argName])) {
                    $arguments[] = in_array($type['name'], ['int', 'float'])
                        ? (float)$params[$argName]
                        : $params[$argName];
                } else if (isset($type['default'])) {
                    $arguments[] = $type['default'];
                } else if (isset($type['null'])) {
                    $arguments[] = null;
                } else if (isset($type['builtIn'])) {
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
                    $arguments[] = $this->resolve($type['name']);
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
}
