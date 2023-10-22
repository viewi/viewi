<?php

namespace Viewi\PhpJsFunctions\Math;

use Viewi\JsTranspile\BaseFunction;

class Rad2deg extends BaseFunction
{
    public static string $name = 'rad2deg';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Rad2deg.js';
        return file_get_contents($jsToInclude);
    }
}
