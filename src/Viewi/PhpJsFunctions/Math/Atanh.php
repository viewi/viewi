<?php

namespace Viewi\PhpJsFunctions\Math;

use Viewi\JsTranspile\BaseFunction;

class Atanh extends BaseFunction
{
    public static string $name = 'atanh';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Atanh.js';
        return file_get_contents($jsToInclude);
    }
}
