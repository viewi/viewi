<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class ParseStr extends BaseFunctionConverter
{
    public static string $name = 'parse_str';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'ParseStr.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
