<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class QuotedPrintableEncode extends BaseFunctionConverter
{
    public static string $name = 'quoted_printable_encode';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'QuotedPrintableEncode.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
