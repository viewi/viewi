<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayFillKeys extends BaseFunction
{
    public static string $name = 'array_fill_keys';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayFillKeys.js';
        return file_get_contents($jsToInclude);
    }
}
