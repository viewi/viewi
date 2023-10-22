<?php

namespace Viewi\PhpJsFunctions\Var;

use Viewi\JsTranspile\BaseFunction;

class IsNull extends BaseFunction
{
    public static string $name = 'is_null';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'IsNull.js';
        return file_get_contents($jsToInclude);
    }
}
