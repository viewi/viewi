<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayUnique extends BaseFunction
{
    public static string $name = 'array_unique';

    public static function getUses(): array
    {
        return ['_php_array_entries', '_php_array', '_php_compare', '_php_cast_float', '_phpCastString'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayUnique.js';
        return file_get_contents($jsToInclude);
    }
}
