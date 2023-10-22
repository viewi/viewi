<?php

namespace Viewi\PhpJsFunctions\Math;

use Viewi\JsTranspile\BaseFunction;

class Bindec extends BaseFunction
{
    public static string $name = 'bindec';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Bindec.js';
        return file_get_contents($jsToInclude);
    }
}
