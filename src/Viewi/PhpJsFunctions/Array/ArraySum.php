<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArraySum extends BaseFunction
{
    public static string $name = 'array_sum';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArraySum.js';
        return file_get_contents($jsToInclude);
    }
}
