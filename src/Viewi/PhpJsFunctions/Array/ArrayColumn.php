<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayColumn extends BaseFunction
{
    public static string $name = 'array_column';

    public static function getUses(): array
    {
        return ['_php_array_entries', '_php_array', '_php_array_key', '_phpCastString'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayColumn.js';
        return file_get_contents($jsToInclude);
    }
}
