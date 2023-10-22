<?php

namespace Viewi\PhpJsFunctions\Math;

use Viewi\JsTranspile\BaseFunction;

class Sinh extends BaseFunction
{
    public static string $name = 'sinh';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Sinh.js';
        return file_get_contents($jsToInclude);
    }
}
