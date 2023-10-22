<?php

namespace Viewi\PhpJsFunctions\Var;

use Viewi\JsTranspile\BaseFunction;

class IsInteger extends BaseFunction
{
    public static string $name = 'is_integer';

    public static function getUses(): array
    {
        return ['is_int'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'IsInteger.js';
        return file_get_contents($jsToInclude);
    }
}
