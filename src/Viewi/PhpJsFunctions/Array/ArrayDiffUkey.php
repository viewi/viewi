<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayDiffUkey extends BaseFunction
{
    public static string $name = 'array_diff_ukey';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayDiffUkey.js';
        return file_get_contents($jsToInclude);
    }
}
