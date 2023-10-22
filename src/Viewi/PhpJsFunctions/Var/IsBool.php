<?php

namespace Viewi\PhpJsFunctions\Var;

use Viewi\JsTranspile\BaseFunction;

class IsBool extends BaseFunction
{
    public static string $name = 'is_bool';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'IsBool.js';
        return file_get_contents($jsToInclude);
    }
}
