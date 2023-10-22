<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArraySplice extends BaseFunction
{
    public static string $name = 'array_splice';

    public static function getUses(): array
    {
        return ['is_int'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArraySplice.js';
        return file_get_contents($jsToInclude);
    }
}
