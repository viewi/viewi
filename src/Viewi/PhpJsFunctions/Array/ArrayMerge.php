<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayMerge extends BaseFunction
{
    public static string $name = 'array_merge';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayMerge.js';
        return file_get_contents($jsToInclude);
    }
}
