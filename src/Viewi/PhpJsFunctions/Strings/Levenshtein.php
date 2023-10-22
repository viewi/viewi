<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class Levenshtein extends BaseFunction
{
    public static string $name = 'levenshtein';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Levenshtein.js';
        return file_get_contents($jsToInclude);
    }
}
