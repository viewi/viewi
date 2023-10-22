<?php

namespace Viewi\PhpJsFunctions\Math;

use Viewi\JsTranspile\BaseFunction;

class Sqrt extends BaseFunction
{
    public static string $name = 'sqrt';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Sqrt.js';
        return file_get_contents($jsToInclude);
    }
}
