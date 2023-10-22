<?php

namespace Viewi\PhpJsFunctions\Math;

use Viewi\JsTranspile\BaseFunction;

class Log1p extends BaseFunction
{
    public static string $name = 'log1p';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Log1p.js';
        return file_get_contents($jsToInclude);
    }
}
