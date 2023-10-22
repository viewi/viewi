<?php

namespace Viewi\PhpJsFunctions\Strings;

use Viewi\JsTranspile\BaseFunction;

class QuotedPrintableEncode extends BaseFunction
{
    public static string $name = 'quoted_printable_encode';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'QuotedPrintableEncode.js';
        return file_get_contents($jsToInclude);
    }
}
