<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayReplaceRecursive extends BaseFunction
{
    public static string $name = 'array_replace_recursive';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayReplaceRecursive.js';
        return file_get_contents($jsToInclude);
    }
}
