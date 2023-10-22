<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayFlip extends BaseFunction
{
    public static string $name = 'array_flip';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayFlip.js';
        return file_get_contents($jsToInclude);
    }
}
