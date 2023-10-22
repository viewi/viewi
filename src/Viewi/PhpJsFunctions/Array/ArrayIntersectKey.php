<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayIntersectKey extends BaseFunction
{
    public static string $name = 'array_intersect_key';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayIntersectKey.js';
        return file_get_contents($jsToInclude);
    }
}
