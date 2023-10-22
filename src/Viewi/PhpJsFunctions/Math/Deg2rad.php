<?php

namespace Viewi\PhpJsFunctions\Math;

use Viewi\JsTranspile\BaseFunction;

class Deg2rad extends BaseFunction
{
    public static string $name = 'deg2rad';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Deg2rad.js';
        return file_get_contents($jsToInclude);
    }
}
