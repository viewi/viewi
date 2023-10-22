<?php

namespace Viewi\PhpJsFunctions\Math;

use Viewi\JsTranspile\BaseFunction;

class Expm1 extends BaseFunction
{
    public static string $name = 'expm1';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Expm1.js';
        return file_get_contents($jsToInclude);
    }
}
