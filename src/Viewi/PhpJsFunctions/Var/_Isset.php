<?php

namespace Viewi\PhpJsFunctions\Var;

use Viewi\JsTranspile\BaseFunction;

class _Isset extends BaseFunction
{
    public static string $name = 'isset';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . '_Isset.js';
        return file_get_contents($jsToInclude);
    }
}
