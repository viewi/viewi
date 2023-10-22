<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Soundex extends BaseFunction
{
    public static string $name = 'soundex';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Soundex.js';
        return file_get_contents($jsToInclude);
    }
}
