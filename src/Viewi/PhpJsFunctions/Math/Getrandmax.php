<?php

namespace Viewi\PhpJsFunctions\Math;

use Viewi\JsTranspile\BaseFunction;

class Getrandmax extends BaseFunction
{
    public static string $name = 'getrandmax';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Getrandmax.js';
        return file_get_contents($jsToInclude);
    }
}
