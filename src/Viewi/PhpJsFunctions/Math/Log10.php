<?php

namespace Viewi\PhpJsFunctions\Math;

use Viewi\JsTranspile\BaseFunction;

class Log10 extends BaseFunction
{
    public static string $name = 'log10';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Log10.js';
        return file_get_contents($jsToInclude);
    }
}
