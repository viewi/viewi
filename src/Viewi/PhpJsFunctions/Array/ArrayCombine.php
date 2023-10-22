<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayCombine extends BaseFunction
{
    public static string $name = 'array_combine';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayCombine.js';
        return file_get_contents($jsToInclude);
    }
}
