<?php

namespace Viewi\PhpJsFunctions\Math;

use Viewi\JsTranspile\BaseFunction;

class IsFinite extends BaseFunction
{
    public static string $name = 'is_finite';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'IsFinite.js';
        return file_get_contents($jsToInclude);
    }
}
