<?php

namespace Viewi\PhpJsFunctions\Math;

use Viewi\JsTranspile\BaseFunction;

class Ceil extends BaseFunction
{
    public static string $name = 'ceil';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Ceil.js';
        return file_get_contents($jsToInclude);
    }
}
