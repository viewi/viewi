<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class InArray extends BaseFunction
{
    public static string $name = 'in_array';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'InArray.js';
        return file_get_contents($jsToInclude);
    }
}
