<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayShift extends BaseFunction
{
    public static string $name = 'array_shift';

    public static function getUses(): array
    {
        return ['_php_array_entries', '_php_array', '_php_array_set'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayShift.js';
        return file_get_contents($jsToInclude);
    }
}
