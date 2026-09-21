<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayMultisort extends BaseFunction
{
    public static string $name = 'array_multisort';

    public static function getUses(): array
    {
        return ['_php_array_entries', '_php_array', '_php_array_set', '_php_sort_compare'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayMultisort.js';
        return file_get_contents($jsToInclude);
    }
}
