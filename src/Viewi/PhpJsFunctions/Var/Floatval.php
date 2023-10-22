<?php

namespace Viewi\PhpJsFunctions\Var;

use Viewi\JsTranspile\BaseFunction;

class Floatval extends BaseFunction
{
    public static string $name = 'floatval';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Floatval.js';
        return file_get_contents($jsToInclude);
    }
}
