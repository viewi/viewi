<?php

namespace Viewi\PhpJsFunctions\Math;

use Viewi\JsTranspile\BaseFunction;

class Hexdec extends BaseFunction
{
    public static string $name = 'hexdec';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Hexdec.js';
        return file_get_contents($jsToInclude);
    }
}
