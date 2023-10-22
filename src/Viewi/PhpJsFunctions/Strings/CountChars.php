<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class CountChars extends BaseFunction
{
    public static string $name = 'count_chars';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'CountChars.js';
        return file_get_contents($jsToInclude);
    }
}
