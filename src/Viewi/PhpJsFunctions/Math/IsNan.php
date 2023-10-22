<?php

namespace Viewi\PhpJsFunctions\Math;

use Viewi\JsTranspile\BaseFunction;

class IsNan extends BaseFunction
{
    public static string $name = 'is_nan';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'IsNan.js';
        return file_get_contents($jsToInclude);
    }
}
