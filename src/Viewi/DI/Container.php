<?php

namespace Viewi\DI;

class Container implements IContainer
{
    private array $services = [];

    public function getAll(): array
    {
        return $this->services;
    }

    public function get(string $type)
    {
        if (array_key_exists($type, $this->services)) {
            return $this->services[$type];
        }
        return null;
    }

    public function set(string $type, $instance)
    {
        $this->services[$type] = $instance;
    }
}
