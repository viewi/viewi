<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArraySlice extends BaseFunction
{
    public static string $name = 'array_slice';

    public static function getUses(): array
    {
        return ['is_int'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArraySlice.js';
        return file_get_contents($jsToInclude);
    }
}
