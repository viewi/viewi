<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayDiffKey extends BaseFunction
{
    public static string $name = 'array_diff_key';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayDiffKey.js';
        return file_get_contents($jsToInclude);
    }
}
