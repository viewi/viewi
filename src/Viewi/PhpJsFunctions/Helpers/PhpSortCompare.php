<?php

namespace Viewi\PhpJsFunctions\Helpers;

use Viewi\JsTranspile\BaseFunction;

class PhpSortCompare extends BaseFunction
{
    public static string $name = '_php_sort_compare';

    public static function getUses(): array
    {
        return ['_php_compare', '_php_cast_float', '_phpCastString', 'strnatcmp', 'strnatcasecmp'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'PhpSortCompare.js';
        return file_get_contents($jsToInclude);
    }
}
