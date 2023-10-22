<?php

namespace Viewi\PhpJsFunctions\Math;

use Viewi\JsTranspile\BaseFunction;

class Hypot extends BaseFunction
{
    public static string $name = 'hypot';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Hypot.js';
        return file_get_contents($jsToInclude);
    }
}
