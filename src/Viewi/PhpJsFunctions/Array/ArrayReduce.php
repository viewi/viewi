<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayReduce extends BaseFunction
{
    public static string $name = 'array_reduce';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayReduce.js';
        return file_get_contents($jsToInclude);
    }
}
