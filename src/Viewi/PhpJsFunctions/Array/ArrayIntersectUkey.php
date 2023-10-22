<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayIntersectUkey extends BaseFunction
{
    public static string $name = 'array_intersect_ukey';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayIntersectUkey.js';
        return file_get_contents($jsToInclude);
    }
}
