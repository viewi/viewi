<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Log1p extends BaseFunctionConverter
{
    public static string $name = 'log1p';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Log1p.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
