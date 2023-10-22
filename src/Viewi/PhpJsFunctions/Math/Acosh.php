<?php

namespace Viewi\PhpJsFunctions\Math;

use Viewi\JsTranspile\BaseFunction;

class Acosh extends BaseFunction
{
    public static string $name = 'acosh';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Acosh.js';
        return file_get_contents($jsToInclude);
    }
}
