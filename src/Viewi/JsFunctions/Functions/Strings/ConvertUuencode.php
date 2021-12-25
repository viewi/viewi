<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class ConvertUuencode extends BaseFunctionConverter
{
    public static string $name = 'convert_uuencode';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'ConvertUuencode.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
