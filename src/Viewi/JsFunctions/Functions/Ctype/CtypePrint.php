<?php

namespace Viewi\JsFunctions\Functions\Ctype;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class CtypePrint extends BaseFunctionConverter
{
    public static string $name = 'ctype_print';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'CtypePrint.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
