<?php

namespace Viewi\JsFunctions\Functions\Datetime;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Date extends BaseFunctionConverter
{
    public static string $name = 'date';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Date.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
