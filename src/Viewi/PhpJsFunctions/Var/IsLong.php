<?php

namespace Viewi\PhpJsFunctions\Var;

use Viewi\JsTranspile\BaseFunction;

class IsLong extends BaseFunction
{
    public static string $name = 'is_long';

    public static function getUses(): array
    {
        return ['is_float'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'IsLong.js';
        return file_get_contents($jsToInclude);
    }
}
