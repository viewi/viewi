<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayFilter extends BaseFunction
{
    public static string $name = 'array_filter';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayFilter.js';
        return file_get_contents($jsToInclude);
    }
}
