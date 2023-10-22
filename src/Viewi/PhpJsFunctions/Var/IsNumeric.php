<?php

namespace Viewi\PhpJsFunctions\Var;

use Viewi\JsTranspile\BaseFunction;

class IsNumeric extends BaseFunction
{
    public static string $name = 'is_numeric';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'IsNumeric.js';
        return file_get_contents($jsToInclude);
    }
}
