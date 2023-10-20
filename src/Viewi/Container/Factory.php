<?php

namespace Viewi\Container;

class Factory
{
    private array $register = [];

    public function add(string $name, callable $factory)
    {
        $name = strpos($name, '\\') !== false ?
            substr(strrchr($name, "\\"), 1)
            : $name;
        $this->register[$name] = $factory;
    }

    public function get(string $name)
    {
        $name = strpos($name, '\\') !== false ?
            substr(strrchr($name, "\\"), 1)
            : $name;
        return $this->register[$name];
    }

    public function has(string $name)
    {
        $name = strpos($name, '\\') !== false ?
            substr(strrchr($name, "\\"), 1)
            : $name;
        return isset($this->register[$name]);
    }
}
