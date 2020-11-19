<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class LcgValue extends BaseFunctionConverter
{
    public static string $name = 'lcg_value';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'LcgValue.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
