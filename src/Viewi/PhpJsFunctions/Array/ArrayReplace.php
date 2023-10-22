<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayReplace extends BaseFunction
{
    public static string $name = 'array_replace';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayReplace.js';
        return file_get_contents($jsToInclude);
    }
}
