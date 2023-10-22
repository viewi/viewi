<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayCountValues extends BaseFunction
{
    public static string $name = 'array_count_values';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayCountValues.js';
        return file_get_contents($jsToInclude);
    }
}
