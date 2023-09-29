<?php

namespace Viewi;

use Exception;

class Engine
{
    private bool $ready = false;
    private array $meta = [];

    public function __construct(private string $buildPath)
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
        /**
         * @var array{inputs: array, components: array}
         */
        $componentMeta = $this->meta['components'][$component];
        $fullClassName = $componentMeta['Namespace'] . '\\' . $componentMeta['Name'];
        $classInstance = new $fullClassName();
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
}
