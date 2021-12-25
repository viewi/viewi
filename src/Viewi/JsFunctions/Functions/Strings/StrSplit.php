<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class StrSplit extends BaseFunctionConverter
{
    public static string $name = 'str_split';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'StrSplit.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
