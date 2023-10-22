<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayKeys extends BaseFunction
{
    public static string $name = 'array_keys';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayKeys.js';
        return file_get_contents($jsToInclude);
    }
}
