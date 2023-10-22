<?php

namespace Viewi\PhpJsFunctions\Math;

use Viewi\JsTranspile\BaseFunction;

class Pow extends BaseFunction
{
    public static string $name = 'pow';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Pow.js';
        return file_get_contents($jsToInclude);
    }
}
