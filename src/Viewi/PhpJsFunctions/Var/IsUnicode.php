<?php

namespace Viewi\PhpJsFunctions\Var;

use Viewi\JsTranspile\BaseFunction;

class IsUnicode extends BaseFunction
{
    public static string $name = 'is_unicode';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'IsUnicode.js';
        return file_get_contents($jsToInclude);
    }
}
