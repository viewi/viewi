<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayUintersect extends BaseFunction
{
    public static string $name = 'array_uintersect';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayUintersect.js';
        return file_get_contents($jsToInclude);
    }
}
