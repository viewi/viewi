<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class NumberFormat extends BaseFunctionConverter
{
    public static string $name = 'number_format';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'NumberFormat.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
