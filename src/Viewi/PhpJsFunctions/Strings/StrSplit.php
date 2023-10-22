<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class StrSplit extends BaseFunction
{
    public static string $name = 'str_split';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'StrSplit.js';
        return file_get_contents($jsToInclude);
    }
}
