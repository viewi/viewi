<?php

namespace Viewi\PhpJsFunctions\Helpers;

use Viewi\JsTranspile\BaseFunction;

class PhpSort extends BaseFunction
{
    public static string $name = '_php_sort';

    public static function getUses(): array
    {
        return ['_php_sort_compare', '_php_array_entries', '_php_array', '_php_array_set'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'PhpSort.js';
        return file_get_contents($jsToInclude);
    }
}
