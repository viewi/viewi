<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayIntersectUassoc extends BaseFunction
{
    public static string $name = 'array_intersect_uassoc';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayIntersectUassoc.js';
        return file_get_contents($jsToInclude);
    }
}
