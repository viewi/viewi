<?php

namespace Viewi\PhpJsFunctions\Var;

use Viewi\JsTranspile\BaseFunction;

class IsScalar extends BaseFunction
{
    public static string $name = 'is_scalar';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'IsScalar.js';
        return file_get_contents($jsToInclude);
    }
}
