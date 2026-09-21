<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayMergeRecursive extends BaseFunction
{
    public static string $name = 'array_merge_recursive';

    public static function getUses(): array
    {
        return ['_php_array_entries', '_php_array'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayMergeRecursive.js';
        return file_get_contents($jsToInclude);
    }
}
