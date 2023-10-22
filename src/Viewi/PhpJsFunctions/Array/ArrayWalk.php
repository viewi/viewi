<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayWalk extends BaseFunction
{
    public static string $name = 'array_walk';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayWalk.js';
        return file_get_contents($jsToInclude);
    }
}
