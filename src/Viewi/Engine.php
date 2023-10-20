<?php

namespace Viewi;

use Exception;
use Viewi\Components\BaseComponent;
use Viewi\Container\Factory;
use Viewi\DI\Scoped;
use Viewi\DI\Singleton;

class Engine
{
    private bool $ready = false;
    private array $meta = [];
    private array $DIContainer = [];
    private int $instanceIdCounter = 0;

    public function __construct(private string $buildPath, private Factory $factory)
    {
    }

    public function render(string $component, array $params = [])
    {
        if (!$this->ready) {
            $this->meta = require $this->buildPath . DIRECTORY_SEPARATOR . 'components.php';
            $this->ready = true;
        }
        $component = strpos($component, '\\') !== false ?
            substr(strrchr($component, "\\"), 1)
            : $component;
        return $this->renderComponent($component, $params, [], []);
    }

    public function renderSlot($component, $scope, $slotFunc, $parentSlots)
    {
        return $slotFunc($this, $component, $parentSlots, $scope);
    }

    public function renderComponent(string $component, $props, $slots, $scope)
    {
        if (
            !isset($this->meta['components'][$component])
            || !isset($this->meta['components'][$component]['Function'])
        ) {
            throw new Exception("Component '$component' not found.");
        }
        // Helpers::debug([$componentMeta]);
        $classInstance = $this->resolve($component);
        /**
         * @var array{inputs: array, components: array}
         */
        $componentMeta = $this->meta['components'][$component];
        if (isset($componentMeta['hooks']['init'])) {
            $classInstance->init();
        }
        include_once $this->buildPath . DIRECTORY_SEPARATOR . $componentMeta['Path'];
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

    /**
     * 
     * @param array{inputs: array, components: array} $componentMeta 
     * @return mixed 
     */
    public function resolve(string $name, array $params = [])
    {
        if ($this->factory->has($name)) {
            $constructor = $this->factory->get($name);
            return $constructor($this);
        }

        if (!isset($this->meta['components'][$name])) {
            throw new Exception("Can not resolve instance for type '$name'");
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
        if (empty($componentMeta['dependencies'])) {
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
        return $instance;
    }
}
