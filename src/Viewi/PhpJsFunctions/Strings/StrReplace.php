<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class StrReplace extends BaseFunction
{
    public static string $name = 'str_replace';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'StrReplace.js';
        return file_get_contents($jsToInclude);
    }
}
