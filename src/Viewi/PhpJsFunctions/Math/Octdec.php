<?php

namespace Viewi\PhpJsFunctions\Math;

use Viewi\JsTranspile\BaseFunction;

class Octdec extends BaseFunction
{
    public static string $name = 'octdec';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Octdec.js';
        return file_get_contents($jsToInclude);
    }
}
