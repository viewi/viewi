<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayKeyExists extends BaseFunction
{
    public static string $name = 'array_key_exists';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayKeyExists.js';
        return file_get_contents($jsToInclude);
    }
}
