<?php

namespace Viewi\PhpJsFunctions\Math;

use Viewi\JsTranspile\BaseFunction;

class Decbin extends BaseFunction
{
    public static string $name = 'decbin';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Decbin.js';
        return file_get_contents($jsToInclude);
    }
}
