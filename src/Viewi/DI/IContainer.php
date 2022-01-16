<?php

namespace Viewi\DI;

interface IContainer
{
    function set(string $type, $instance);
    function get(string $type);
    function getAll(): array;
}