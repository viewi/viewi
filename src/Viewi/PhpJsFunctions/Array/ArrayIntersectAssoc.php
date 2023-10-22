<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayIntersectAssoc extends BaseFunction
{
    public static string $name = 'array_intersect_assoc';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayIntersectAssoc.js';
        return file_get_contents($jsToInclude);
    }
}
