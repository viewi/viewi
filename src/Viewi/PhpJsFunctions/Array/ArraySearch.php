<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArraySearch extends BaseFunction
{
    public static string $name = 'array_search';

    public static function getUses(): array
    {
        return ['_php_compare', '_php_array_key'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArraySearch.js';
        return file_get_contents($jsToInclude);
    }
}
