<?php

namespace Viewi\PhpJsFunctions\Array;

use Viewi\JsTranspile\BaseFunction;

class Uasort extends BaseFunction
{
    public static string $name = 'uasort';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Uasort.js';
        return file_get_contents($jsToInclude);
    }
}
