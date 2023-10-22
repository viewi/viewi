<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class ParseStr extends BaseFunction
{
    public static string $name = 'parse_str';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ParseStr.js';
        return file_get_contents($jsToInclude);
    }
}
