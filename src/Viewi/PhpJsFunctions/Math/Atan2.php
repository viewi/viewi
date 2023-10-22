<?php

namespace Viewi\PhpJsFunctions\Math;

use Viewi\JsTranspile\BaseFunction;

class Atan2 extends BaseFunction
{
    public static string $name = 'atan2';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Atan2.js';
        return file_get_contents($jsToInclude);
    }
}
