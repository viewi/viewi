<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayReverse extends BaseFunction
{
    public static string $name = 'array_reverse';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayReverse.js';
        return file_get_contents($jsToInclude);
    }
}
