<?php

namespace Viewi\PhpJsFunctions\Var;

use Viewi\JsTranspile\BaseFunction;

class IsBinary extends BaseFunction
{
    public static string $name = 'is_binary';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'IsBinary.js';
        return file_get_contents($jsToInclude);
    }
}
