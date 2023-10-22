<?php

namespace Viewi\PhpJsFunctions\Math;

use Viewi\JsTranspile\BaseFunction;

class Asinh extends BaseFunction
{
    public static string $name = 'asinh';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Asinh.js';
        return file_get_contents($jsToInclude);
    }
}
