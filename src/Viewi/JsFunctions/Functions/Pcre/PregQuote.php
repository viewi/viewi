<?php

namespace Viewi\JsFunctions\Functions\Pcre;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class PregQuote extends BaseFunctionConverter
{
    public static string $name = 'preg_quote';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'PregQuote.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
