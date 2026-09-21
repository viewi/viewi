<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayUnshift extends BaseFunction
{
    public static string $name = 'array_unshift';

    public static function getUses(): array
    {
        return ['_php_array_entries', '_php_array', '_php_array_set'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayUnshift.js';
        return file_get_contents($jsToInclude);
    }
}
