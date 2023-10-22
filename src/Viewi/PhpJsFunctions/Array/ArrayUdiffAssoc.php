<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayUdiffAssoc extends BaseFunction
{
    public static string $name = 'array_udiff_assoc';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayUdiffAssoc.js';
        return file_get_contents($jsToInclude);
    }
}
