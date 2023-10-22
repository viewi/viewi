<?php

namespace Viewi\PhpJsFunctions\Math;

use Viewi\JsTranspile\BaseFunction;

class Max extends BaseFunction
{
    public static string $name = 'max';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Max.js';
        return file_get_contents($jsToInclude);
    }
}
