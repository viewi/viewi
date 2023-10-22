<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayWalkRecursive extends BaseFunction
{
    public static string $name = 'array_walk_recursive';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayWalkRecursive.js';
        return file_get_contents($jsToInclude);
    }
}
