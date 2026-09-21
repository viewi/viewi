<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayMap extends BaseFunction
{
    public static string $name = 'array_map';

    public static function getUses(): array
    {
        return ['_php_array_entries', '_php_array'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayMap.js';
        return file_get_contents($jsToInclude);
    }
}
