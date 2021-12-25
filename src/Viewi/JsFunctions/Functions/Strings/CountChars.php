<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class CountChars extends BaseFunctionConverter
{
    public static string $name = 'count_chars';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'CountChars.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
