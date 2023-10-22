<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class ArrayUdiff extends BaseFunction
{
    public static string $name = 'array_udiff';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayUdiff.js';
        return file_get_contents($jsToInclude);
    }
}
