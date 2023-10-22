<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayUnique extends BaseFunction
{
    public static string $name = 'array_unique';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayUnique.js';
        return file_get_contents($jsToInclude);
    }
}
