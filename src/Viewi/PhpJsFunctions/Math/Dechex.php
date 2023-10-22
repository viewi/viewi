<?php

namespace Viewi\PhpJsFunctions\Math;

use Viewi\JsTranspile\BaseFunction;

class Dechex extends BaseFunction
{
    public static string $name = 'dechex';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Dechex.js';
        return file_get_contents($jsToInclude);
    }
}
