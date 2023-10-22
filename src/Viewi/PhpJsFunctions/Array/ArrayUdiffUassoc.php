<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayUdiffUassoc extends BaseFunction
{
    public static string $name = 'array_udiff_uassoc';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayUdiffUassoc.js';
        return file_get_contents($jsToInclude);
    }
}
