<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayUintersectUassoc extends BaseFunction
{
    public static string $name = 'array_uintersect_uassoc';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayUintersectUassoc.js';
        return file_get_contents($jsToInclude);
    }
}
