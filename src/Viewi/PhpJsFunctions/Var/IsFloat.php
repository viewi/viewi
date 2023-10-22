<?php

namespace Viewi\PhpJsFunctions\Var;

use Viewi\JsTranspile\BaseFunction;

class IsFloat extends BaseFunction
{
    public static string $name = 'is_float';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'IsFloat.js';
        return file_get_contents($jsToInclude);
    }
}
