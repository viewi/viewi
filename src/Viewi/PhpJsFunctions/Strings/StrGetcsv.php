<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class StrGetcsv extends BaseFunction
{
    public static string $name = 'str_getcsv';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'StrGetcsv.js';
        return file_get_contents($jsToInclude);
    }
}
