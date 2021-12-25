<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Asin extends BaseFunctionConverter
{
    public static string $name = 'asin';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Asin.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
